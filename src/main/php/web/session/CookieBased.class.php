<?php namespace web\session;

use util\Secret;
use web\Cookie;
use web\session\cookie\Session;

class CookieBased extends Sessions {
  private $key, $signing;
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
   * @param  string|util.Secret $signing
   */
  public function __construct($key, $signing) {
    $this->key= $key instanceof Secret ? $key : new Secret($key);
    $this->signing= $signing instanceof Secret ? $signing : new Secret($signing);
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
    $response->cookie((new Cookie($this->name(), $session->id()))
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
    if ($r= strlen($input) % 4) {
      $input.= str_repeat('=', 4 - $r);
    }
    return base64_decode(strtr($input, '_-', '/+'));
  }

  /**
   * Signs given input and returns it in URL-safe base64
   * 
   * @param  string $input
   * @return string
   */
  private function sign($input) {
    return $this->encode(hash_hmac('SHA256', $input, $this->signing->reveal(), true));
  }

  /**
   * Creates the serialized form
   * 
   * @see    https://www.rfc-editor.org/rfc/rfc7515.html
   * @param  [:var] $claims
   * @return string
   */
  public function serialize($claims) {
    $payload= $this->encode('{"alg":"HS256","typ":"JWT"}').'.'.$this->encode(json_encode($claims));
    return $payload.'.'.$this->sign($payload);
  }

  /**
   * Creates a session
   *
   * @return web.session.Session
   */
  public function create() {
    $now= time();
    return new Session($this, ['iat' => $now, 'exp' => $now + $this->duration]);
  }

  /**
   * Opens an existing and valid session. 
   *
   * @param  string $id
   * @return ?web.session.ISession
   */
  public function open($id) {

    // ID is a JWT with a "HS256" signature containing an encrypted payload. We
    // ignore the header segment completely as we know how we issued the JWT!
    $segments= explode('.', $id);
    if (3 !== sizeof($segments)) return null;
    if ($segments[2] !== $this->sign($segments[0].'.'.$segments[1])) return null;

    // Check expiry time
    $values= json_decode($this->decode($segments[1]), true);
    if (time() > $values['exp']) return null;

    return new Session($this, $values);
  }
}