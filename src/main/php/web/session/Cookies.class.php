<?php namespace web\session;

use web\Cookie;

class Cookies implements Transport {
  private $attributes= [
    'path'     => '/',
    'secure'   => true,
    'domain'   => null,
    'httpOnly' => true,
    'sameSite' => 'Lax'
  ];

  /** @return [:string] */
  public function attributes() { return $this->attributes; }

  /**
   * Restricts to a given path. Use `/` for all paths on a given domain
   *
   * @param  string $path
   * @return self
   */
  public function path($path) {
    $this->attributes['path']= $path;
    return $this;
  }

  /**
   * Restricts to a given domain. Prefix with `.` to make valid for all subdomains
   *
   * @param  string $domain
   * @return self
   */
  public function domain($domain) {
    $this->attributes['domain']= $domain;
    return $this;
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
   * Switch whether to also make cookies accessible to JavaScript.
   *
   * @param  bool $accessible
   * @return self
   */
  public function accessible($accessible= true) {
    $this->attributes['httpOnly']= !$accessible;
    return $this;
  }

  /**
   * Switch whether to only transmit only to same site; preventing CSRF
   *
   * @param  string $sameSite one of "Strict", "Lax" or null (use the latter to remove)
   * @return self
   */
  public function sameSite($sameSite) {
    $this->attributes['sameSite']= $sameSite;
    return $this;
  }

  /**
   * Locate session attached to request
   *
   * @param  web.session.Sessions $sessions
   * @param  web.Request $request
   * @return ?web.session.ISession
   */
  public function locate($sessions, $request) {
    if ($id= $request->cookie($sessions->name())) {
      return $sessions->open($id);
    }
    return null;
  }

  /**
   * Attach session to response
   *
   * @param  web.session.Sessions $sessions
   * @param  web.Response $response
   * @param  web.session.ISession $session
   * @return void
   */
  public function attach($sessions, $response, $session) {
    $response->cookie((new Cookie($sessions->name(), $session->id()))
      ->maxAge($sessions->duration())
      ->path($this->attributes['path'])
      ->secure($this->attributes['secure'])
      ->domain($this->attributes['domain'])
      ->httpOnly($this->attributes['httpOnly'])
      ->sameSite($this->attributes['sameSite'])
    );
  }

  /**
   * Detach session from response
   *
   * @param  web.session.Sessions $sessions
   * @param  web.Response $response
   * @param  web.session.ISession $session
   * @return void
   */
  public function detach($sessions, $response, $session) {
    $response->cookie((new Cookie($sessions->name(), null))
      ->path($this->attributes['path'])
      ->domain($this->attributes['domain'])
    );
  }
}