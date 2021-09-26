<?php namespace web\session;

use util\TimeSpan;

/**
 * Base class for session factories
 *
 * @test  xp://web.session.unittest.SessionsTest
 */
abstract class Sessions {
  protected $duration= 86400;
  protected $name= 'session';
  protected $transport= null;

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
   * Sets the session transport
   *
   * @param  web.session.Transport $transport
   * @return self
   */
  public function via($transport) {
    $this->transport= $transport;
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
   * Returns session transport
   *
   * @return web.session.Transport
   */
  public function transport() { return $this->transport ?: $this->transport= new Cookies(); }

  /**
   * Locates an existing and valid session; returns NULL if there is no such session.
   *
   * @param  web.Request $request
   * @return ?web.session.ISession
   */
  public function locate($request) {
    return $this->transport()->locate($this, $request);
  }

  /**
   * Attaches session to response 
   *
   * @param  web.session.ISession $session
   * @param  web.Response $response
   * @return void
   */
  public function attach($session, $response) {
    $this->transport()->attach($this, $response, $session);
  }

  /**
   * Detaches session from response 
   *
   * @param  web.session.ISession $session
   * @param  web.Response $response
   * @return void
   */
  public function detach($session, $response) {
    $this->transport()->detach($this, $response, $session);
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