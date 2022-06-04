<?php namespace web\session;

use web\session\testing\Implementation;

/**
 * Session factory used for testing. Keeps session data in a backing map
 * in memory.
 *
 * @test  web.session.unittest.ForTestingTest
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
    return $this->sessions[$id]= new Implementation($this, $id, true, time() + $this->duration);
  }

  /**
   * Opens an existing and valid session. 
   *
   * @param  string $id
   * @return ?web.session.Session
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
   * @return web.session.Session[]
   */
  public function all() { return $this->sessions; }
}