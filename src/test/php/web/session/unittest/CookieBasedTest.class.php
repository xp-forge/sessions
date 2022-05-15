<?php namespace web\session\unittest;

use util\Secret;
use web\session\CookieBased;

class CookieBasedTest extends SessionsTest {

  /** @return web.session.Sessions */
  protected function fixture() { return new CookieBased(new Secret('tlw3/ELaLfu3kmpzQJ0MDCdRG2b8Le+X')); }

  #[Test]
  public function id_changes_when_registering_values() {
    $session= $this->fixture()->create();
    $id= $session->id();
    $session->register('key', 'value');

    $this->assertNotEquals($id, $session->id());
  }

  #[Test]
  public function id_changes_when_removing_registered_values() {
    $session= $this->fixture()->create();
    $id= $session->id();
    $session->register('key', 'value');
    $session->remove('key');

    $this->assertNotEquals($id, $session->id());
  }
}