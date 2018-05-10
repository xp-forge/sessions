<?php namespace web\session;

use web\session\testing\Session;

/**
 * Session factory used for testing. Keeps session data in a backing map
 * in memory.
 *
 * @test  xp://web.session.unittest.ForTestingTest
 */
class ForTesting extends Sessions {
  private $sessions= [];

  /**
   * Creates a session
   *
   * @return web.session.Session
   */
  public function create() {
    $id= uniqid(microtime(true));
    return $this->sessions[$id]= new Session($this, $id, true, time() + $this->duration);
  }

  /**
   * Locates an existing session; returns NULL if there is no such session.
   *
   * @param  web.Request $request
   * @return web.session.Session
   */
  public function locate($request) {
    $id= $this->id($request);
    if (isset($this->sessions[$id])) {
      if ($this->sessions[$id]->valid()) return $this->sessions[$id];
      unset($this->sessions[$id]);
    }
    return null;
  }

  /**
   * Returns all sessions maintained by this factory
   *
   * @return web.session.Session[]
   */
  public function all() { return $this->sessions; }
}