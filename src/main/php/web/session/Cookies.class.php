<?php namespace web\session;

use web\Cookie;

/** @see web.sessions.Sessions::cookies() */
class Cookies {
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
   * Creates a cookie with all the attributes set on this instance.
   *
   * @param  string $name
   * @param  ?string $value pass NULL to delete
   * @return web.Cookie
   */
  public function create($name, $value) {
    return (new Cookie($name, $value))
      ->path($this->attributes['path'])
      ->secure($this->attributes['secure'])
      ->domain($this->attributes['domain'])
      ->httpOnly($this->attributes['httpOnly'])
      ->sameSite($this->attributes['sameSite'])
    ;
  }
}