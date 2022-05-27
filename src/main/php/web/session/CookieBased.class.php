<?php namespace web\session;

use lang\FormatException;
use util\Secret;
use web\Cookie;
use web\session\cookie\{Session, Encryption};

class CookieBased extends Sessions {
  private $encryption;
  private $attributes= [
    'path'     => '/',
    'secure'   => true,
    'domain'   => null,
    'httpOnly' => true,
    'sameSite' => 'Lax'
  ];

  /**
   * Creates an new cookie-based session
   *
   * @param  string|util.Secret $key
   * @param  ?web.session.cookie.Encryption $encryption
   */
  public function __construct($key, Encryption $encryption= null) {
    $this->encryption= $encryption ?? Encryption::available($key instanceof Secret ? $key : new Secret($key))[0];
  }

  /**
   * Sets the session transport
   *
   * @param  web.session.Transport $transport
   * @return self
   */
  public function via($transport) {

    // Special-case handling for Cookies-transport
    if ($transport instanceof Cookies) {
      $this->attributes= $transport->attributes();
    }

    return parent::via($transport);
  }

  /**
   * Switch whether to also transmit via insecure connections (HTTP).
   *
   * @param  bool $insecure
   * @return self
   */
  public function insecure($insecure= true) {
    $this->attributes['secure']= !$insecure;
    return $this;
  }

  /**
   * Locates an existing and valid session; returns NULL if there is no such session.
   *
   * @param  web.Request $request
   * @return ?web.session.ISession
   */
  public function locate($request) {
    $cookie= $request->cookie($this->name);
    return null === $cookie ? null : $this->open($cookie);
  }

  /**
   * Attaches session to response 
   *
   * @param  web.session.ISession $session
   * @param  web.Response $response
   * @return void
   */
  public function attach($session, $response) {
    $response->cookie((new Cookie($this->name, $session->id()))
      ->maxAge($this->duration())
      ->path($this->attributes['path'])
      ->secure($this->attributes['secure'])
      ->domain($this->attributes['domain'])
      ->httpOnly($this->attributes['httpOnly'])
      ->sameSite($this->attributes['sameSite'])
    );
  }

  /**
   * Detaches session from response 
   *
   * @param  web.session.ISession $session
   * @param  web.Response $response
   * @return void
   */
  public function detach($session, $response) {
    $response->cookie((new Cookie($this->name(), null))
      ->path($this->attributes['path'])
      ->domain($this->attributes['domain'])
    );
  }

  /**
   * URL-safe base64 encoding
   *
   * @see    https://datatracker.ietf.org/doc/html/rfc4648#section-5
   * @param  string $input
   * @return string
   */
  private function encode($input) {
    return rtrim(strtr(base64_encode($input), '/+', '_-'), '=');
  }

  /**
   * URL-safe base64 decoding
   *
   * @param  string $input
   * @return string
   */
  private function decode($input) {
    return base64_decode(strtr($input, '_-', '/+').'===');
  }

  /**
   * Creates the serialized form
   * 
   * @param  [:var] $value
   * @param  int $expire
   * @return string
   */
  public function serialize($values, $expire) {
    return $this->encryption->id().$this->encode($this->encryption->encrypt(json_encode([$values, $expire])));
  }

  /**
   * Creates a session
   *
   * @return web.session.Session
   */
  public function create() {
    $now= time();
    return new Session($this, [], $now + $this->duration);
  }

  /**
   * Opens an existing and valid session. 
   *
   * @param  string $id
   * @return ?web.session.ISession
   */
  public function open($id) {

    // ID[0] is encryption identifier, the rest is the encrypted text. If the
    // identifiers don't match, regard the session as invalid.
    if ($this->encryption->id() !== ($id[0] ?? null)) return null;

    // If the ciphertext has been tampered with, the encryption implementation will
    // raise an exception - handle this silently like an invalid session.
    try {
      $serialized= json_decode($this->encryption->decrypt($this->decode(substr($id, 1))), true);
    } catch (FormatException $e) {
      return null;
    }

    // Check expiry time
    if (time() > $serialized[1]) return null;

    return new Session($this, ...$serialized);
  }
}