<?php namespace web\session\unittest;

use unittest\Test;
use web\session\ForTesting;

class ForTestingTest extends SessionsTest {

  /** @return web.session.Sessions */
  protected function fixture() { return new ForTesting(); }

  #[Test]
  public function all_initially_empty() {
    $this->assertEquals([], $this->fixture()->all());
  }

  #[Test]
  public function all_after_creating_session() {
    $sessions= $this->fixture();
    $created= $sessions->create();
    $this->assertEquals([$created->token() => $created], $sessions->all());
  }
}