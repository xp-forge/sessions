<?php namespace web\session\unittest;

use test\Assert;
use test\Test;
use web\session\{ForTesting, ISession};

class ForTestingTest extends SessionsTest {

  /** @return web.session.Sessions */
  protected function fixture() { return new ForTesting(); }

  #[Test]
  public function all_initially_empty() {
    Assert::equals([], $this->fixture()->all());
  }

  #[Test]
  public function all_after_creating_session() {
    $sessions= $this->fixture();
    $created= $sessions->create();
    Assert::equals([$created->id() => $created], $sessions->all());
  }

  #[Test]
  public function gc_wipes_destroyed_session() {
    $sessions= $this->fixture();
    $created= $sessions->create();
    $created->destroy();
    Assert::equals([], $sessions->all());
  }

  #[Test]
  public function gc_doesnt_wipe_active() {
    $sessions= $this->fixture();
    $created= $sessions->create();
    Assert::equals(0, $sessions->gc());
  }
}