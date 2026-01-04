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


  /** @return int */
  public function gc() {
    $deleted= 0;
    foreach ($this->sessions as $id => $session) {
      if ($session->valid()) continue;
      unset($this->sessions[$id]);
      $deleted++;
    }
    return $deleted;
  }

  /**
   * Creates a session
   *
   * @return web.session.Persistence
   */
  public function create() {
    $id= uniqid(microtime(true));
    return $this->sessions[$id]= new Session($this, $id, true, time() + $this->duration);
  }

  /**
   * Opens an existing and valid session. 
   *
   * @param  string $id
   * @return ?web.session.Persistence
   */
  public function open($id) {
    if (isset($this->sessions[$id])) {
      if ($this->sessions[$id]->valid()) return $this->sessions[$id];
      unset($this->sessions[$id]);
    }
    return null;
  }

  /**
   * Returns all sessions maintained by this factory
   *
   * @return web.session.Persistence[]
   */
  public function all() { return $this->sessions; }
}