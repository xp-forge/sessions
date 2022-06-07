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
    $token= uniqid(microtime(true));
    return $this->sessions[$token]= new Implementation($this, time() + $this->duration, $token, true);
  }

  /**
   * Opens an existing and valid session. 
   *
   * @param  string $token
   * @return ?web.session.Session
   */
  public function open($token) {
    if (isset($this->sessions[$token])) {
      if ($this->sessions[$token]->valid()) return $this->sessions[$token]->open();
      unset($this->sessions[$token]);
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