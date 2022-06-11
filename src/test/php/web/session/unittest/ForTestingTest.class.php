<?php namespace web\session\unittest;

use unittest\Test;
use web\session\{ForTesting, ISession};

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
    $this->assertEquals([$created->id() => $created], $sessions->all());
  }

  #[Test]
  public function gc_wipes_destroyed_session() {
    $sessions= $this->fixture();
    $created= $sessions->create();
    $created->destroy();
    $this->assertEquals([], $sessions->all());
  }

  #[Test]
  public function gc_doesnt_wipe_active() {
    $sessions= $this->fixture();
    $created= $sessions->create();
    $this->assertEquals(0, $sessions->gc());
  }
}