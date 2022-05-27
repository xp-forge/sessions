<?php namespace web\session\unittest;

use lang\IllegalArgumentException;
use unittest\{Test, Expect, Values};
use util\Secret;
use web\session\CookieBased;

class CookieBasedTest extends SessionsTest {

  /** @return web.session.Sessions */
  protected function fixture() { return new CookieBased(new Secret('tlw3/ELaLfu3kmpzQJ0MDCdRG2b8Le+X')); }

  #[Test, Expect(IllegalArgumentException::class), Values(['', 'too-short'])]
  public function raises_error_when_key_is_not_long_enough($key) {
    new CookieBased(new Secret($key));
  }

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