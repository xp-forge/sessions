<?php namespace web\session\unittest;

use unittest\{Test, TestCase};
use web\session\Cookies;

class CookiesTest extends TestCase {

  #[Test]
  public function can_create() {
    new Cookies();
  }

  #[Test]
  public function defaults() {
    $fixture= new Cookies();
    $this->assertEquals(
      ['path' => '/', 'secure' => true, 'domain' => null, 'httpOnly' => true, 'sameSite' => 'Lax'],
      $fixture->attributes()
    );
  }

  #[Test]
  public function in_path() {
    $fixture= new Cookies();
    $this->assertEquals('/sub', $fixture->path('/sub')->attributes()['path']);
  }

  #[Test]
  public function under_domain() {
    $fixture= new Cookies();
    $this->assertEquals('example.org', $fixture->domain('example.org')->attributes()['domain']);
  }

  #[Test]
  public function same_site() {
    $fixture= new Cookies();
    $this->assertEquals('Strict', $fixture->sameSite('Strict')->attributes()['sameSite']);
  }

  #[Test]
  public function secure() {
    $fixture= new Cookies();
    $this->assertTrue($fixture->insecure(false)->attributes()['secure']);
  }

  #[Test]
  public function insecure() {
    $fixture= new Cookies();
    $this->assertFalse($fixture->insecure(true)->attributes()['secure']);
  }

  #[Test]
  public function accessible() {
    $fixture= new Cookies();
    $this->assertFalse($fixture->accessible(true)->attributes()['httpOnly']);
  }

  #[Test]
  public function http_only() {
    $fixture= new Cookies();
    $this->assertTrue($fixture->accessible(false)->attributes()['httpOnly']);
  }
}