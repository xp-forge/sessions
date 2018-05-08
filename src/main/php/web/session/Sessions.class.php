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
   * Opens an existing session
   *
   * @param  string $id
   * @return web.session.Session
   * @throws web.session.NoSuchSession
   */
  public abstract function open($id);

  /**
   * Locates an existing session; returns NULL if there is no such session.
   *
   * @param  string $id
   * @return web.session.Session
   */
  public abstract function locate($id);
}