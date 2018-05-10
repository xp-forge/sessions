<?php namespace web\session\unittest;

use web\session\ForTesting;
use web\session\ISession;

class ForTestingTest extends SessionsTest {

  /** @return web.session.Sessions */
  protected function fixture() { return new ForTesting(); }

  #[@test]
  public function all_initially_empty() {
    $this->assertEquals([], $this->fixture()->all());
  }

  #[@test]
  public function all_after_creating_session() {
    $sessions= $this->fixture();
    $created= $sessions->create();
    $this->assertEquals([$created->id() => $created], $sessions->all());
  }
}