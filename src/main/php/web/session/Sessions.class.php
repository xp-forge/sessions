<?php namespace web\session;

use util\TimeSpan;

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
   * Returns session duration in seconds
   *
   * @return int
   */
  public function duration() { return $this->duration; }

  /**
   * Creates a session
   *
   * @return web.session.Session
   */
  public abstract function create();

  /**
   * Locates an existing and valid session; returns NULL if there is no such session.
   *
   * @param  string $id
   * @return web.session.ISession
   */
  public abstract function locate($id);

  /**
   * Opens an existing and valid session. Like `locate()` but raises an exception of
   * there is no such sessions.
   *
   * @param  string $id The session ID
   * @return web.session.ISession
   * @throws web.session.NoSuchSession
   */
  public function open($id) {
    if ($session= $this->locate($id)) return $session;
    throw new NoSuchSession($id);
  }
}