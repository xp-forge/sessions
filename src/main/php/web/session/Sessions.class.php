<?php namespace web\session;

use util\TimeSpan;
use web\Cookie;

/**
 * Base class for session factories
 *
 * @test  xp://web.session.unittest.SessionsTest
 */
abstract class Sessions {
  protected $duration= 86400;
  protected $cookie= 'session';
  protected $path= '/';
  protected $secure= true;
  protected $sameSite= 'Lax';

  /**
   * Sets how long a session should last. Defaults to one day.
   *
   * @param  int|util.TimeSpan $duration
   * @return self
   */
  public function lasting($duration) {
    $this->duration= $duration instanceof TimeSpan ? $duration->getSeconds() : (int)$duration;
    return $this;
  }

  /**
   * Sets the cookie name
   *
   * @param  string $cookie
   * @return self
   */
  public function named($cookie) {
    $this->cookie= $cookie;
    return $this;
  }

  /**
   * Sets path the sessions should be valid for, defaults to "/"
   *
   * @param  string $path
   * @return self
   */
  public function in($path) {
    $this->path= $path;
    return $this;
  }

  /**
   * Sets whether secure transport for cookies should be enabled or disabled.
   *
   * @param  bool $whether
   * @return self
   */
  public function insecure($whether) {
    $this->secure= !$whether;
    return $this;
  }

  /**
   * Switch whether to transmit session cookie only to same site; preventing CSRF
   *
   * @param  string $sameSite one of "Strict", "Lax" or null (use the latter to remove)
   * @return self
   */
  public function sameSite($sameSite) {
    $this->sameSite= $sameSite;
    return $this;
  }

  /**
   * Returns session duration in seconds
   *
   * @return int
   */
  public function duration() { return $this->duration; }

  /**
   * Returns session cookie name
   *
   * @return string
   */
  public function name() { return $this->cookie; }

  /**
   * Returns session cookie path
   *
   * @return string
   */
  public function path() { return $this->path; }

  /**
   * Returns whether session cookie is set with secure flag
   *
   * @return  bool
   */
  public function isSecure() { return $this->secure; }

  /**
   * Returns whether sameSite attribute is set to given value.
   *
   * @param  string $value
   * @return bool
   */
  public function sameSiteIs($value) {
    return $this->sameSite === $value;
  }

  /**
   * Returns session ID from request
   *
   * @param  web.Request $request
   * @return string
   */
  public function id($request) { return $request->cookie($this->cookie); }

  /**
   * Locates an existing and valid session; returns NULL if there is no such session.
   *
   * @param  web.Request $request
   * @return web.session.ISession
   */
  public function locate($request) {
    return ($id= $this->id($request)) ? $this->open($id) : null;
  }

  /**
   * Attaches session ID to response 
   *
   * @param  string $id
   * @param  web.Response $response
   * @return void
   */
  public function attach($id, $response) {
    $response->cookie((new Cookie($this->cookie, $id))->maxAge($this->duration)->path($this->path)->secure($this->secure));
  }

  /**
   * Detaches session ID from response 
   *
   * @param  string $id
   * @param  web.Response $response
   * @return void
   */
  public function detach($id, $response) {
    $response->cookie(new Cookie($this->cookie, null));
  }

  /**
   * Creates a session
   *
   * @return web.session.ISession
   */
  public abstract function create();

  /**
   * Opens an existing and valid session. Returns NULL if there is no such session.
   *
   * @param  string $id
   * @return web.session.ISession
   */
  public abstract function open($id);
}