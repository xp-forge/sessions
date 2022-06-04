<?php namespace web\session;

use util\TimeSpan;

/**
 * Base class for session factories
 *
 * @test  web.session.unittest.SessionsTest
 */
abstract class Sessions {
  protected $duration= 86400;
  protected $name= 'session';
  protected $cookies= null;

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
   * Sets the session name
   *
   * @param  string $name
   * @return self
   */
  public function named($name) {
    $this->name= $name;
    return $this;
  }

  /**
   * Sets the session cookies
   *
   * @param  web.session.Cookies $cookies
   * @return self
   */
  public function via($cookies) {
    $this->cookies= $cookies;
    return $this;
  }

  /**
   * Returns session duration in seconds
   *
   * @return int
   */
  public function duration() { return $this->duration; }

  /**
   * Returns session name
   *
   * @return string
   */
  public function name() { return $this->name; }

  /**
   * Returns session cookies
   *
   * @return web.session.Cookies
   */
  public function cookies() { return $this->cookies ?? $this->cookies= new Cookies(); }

  /**
   * Locates an existing and valid session; returns NULL if there is no such session.
   *
   * @param  web.Request $request
   * @return ?web.session.ISession
   */
  public function locate($request) {
    if ($token= $request->cookie($this->name)) return $this->open($token);
    return null;
  }

  /**
   * Attaches session to response 
   *
   * @param  web.session.ISession $session
   * @param  web.Response $response
   * @return void
   */
  public function attach($session, $response) {
    $response->cookie($this->cookies()->create($this->name, $session->id())->maxAge($this->duration));
  }

  /**
   * Detaches session from response 
   *
   * @param  web.session.ISession $session
   * @param  web.Response $response
   * @return void
   */
  public function detach($session, $response) {
    $response->cookie($this->cookies()->create($this->name, null));
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
   * @param  string $token
   * @return ?web.session.ISession
   */
  public abstract function open($token);
}