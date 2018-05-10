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
   * Returns session ID from request 
   *
   * @param  web.Request $request
   * @return string
   */
  public function id($request) {
    return $request->cookie('session');
  }

  /**
   * Sends session ID
   *
   * @param  web.Reponse
   * @param  string $id
   * @return void
   */
  public function transmit($response, $id) {
    $response->cookie((new Cookie('session', $id))->maxAge($this->duration));
  }

  /**
   * Returns session duration in seconds
   *
   * @return int
   */
  public function duration() { return $this->duration; }

  /**
   * Creates a session
   *
   * @param  web.Response $response
   * @return web.session.Session
   */
  public abstract function create($response);

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