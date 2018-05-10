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
   * Returns session duration in seconds
   *
   * @return int
   */
  public function duration() { return $this->duration; }

  /**
   * Returns session ID from request 
   *
   * @param  web.Request $request
   * @return string
   */
  public function id($request) { return $request->cookie($this->cookie); }

  /**
   * Attaches session ID to response 
   *
   * @param  string $id
   * @param  web.Response $response
   * @return vod
   */
  public function attach($id, $response) {
    $response->cookie((new Cookie($this->cookie, $id))->maxAge($this->duration));
  }

  /**
   * Detaches session ID from response 
   *
   * @param  string $id
   * @param  web.Response $response
   * @return vod
   */
  public function detach($id, $response) {
    $response->cookie(new Cookie($this->cookie, null));
  }

  /**
   * Creates a session
   *
   * @return web.session.Session
   */
  public abstract function create();

  /**
   * Locates an existing and valid session; returns NULL if there is no such session.
   *
   * @param  web.Request $request
   * @return web.session.ISession
   */
  public abstract function locate($request);

  /**
   * Opens an existing and valid session. Like `locate()` but raises an exception of
   * there is no such sessions.
   *
   * @param  web.Request $request
   * @return web.session.ISession
   * @throws web.session.NoSuchSession
   */
  public function open($request) {
    if ($session= $this->locate($request)) return $session;
    throw new NoSuchSession($this->id($request));
  }
}