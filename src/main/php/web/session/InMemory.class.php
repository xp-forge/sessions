<?php namespace web\session;

use web\session\memory\Session;

/**
 * In-Memory session factory
 *
 * @test  xp://web.session.unittest.InMemoryTest
 */
class InMemory extends Sessions {
  private $sessions;

  /**
   * Creates a session
   *
   * @return web.session.Session
   */
  public function create() {
    $id= uniqid(microtime(true));
    return $this->sessions[$id]= new Session($id, time() + $this->duration);
  }

  /**
   * Opens an existing session
   *
   * @param  string $id
   * @return web.session.Session
   * @throws web.session.NoSuchSession
   */
  public function open($id) {
    if (isset($this->sessions[$id])) return $this->sessions[$id];
    throw new NoSuchSession($id);
  }

  /**
   * Locates an existing session; returns NULL if there is no such session.
   *
   * @param  string $id
   * @return web.session.Session
   */
  public function locate($id) {
    return isset($this->sessions[$id]) ? $this->sessions[$id] : null;
  }

  /**
   * Returns all sessions maintained by this factory
   *
   * @return web.session.Session[]
   */
  public function all() { return $this->sessions; }
}