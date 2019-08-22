<?php namespace web\session\unittest;

use unittest\TestCase;
use web\session\Cookies;

class CookiesTest extends TestCase {

  #[@test]
  public function can_create() {
    new Cookies();
  }

  #[@test]
  public function defaults() {
    $fixture= new Cookies();
    $this->assertEquals(
      ['path' => '/', 'secure' => true, 'domain' => null, 'httpOnly' => true, 'sameSite' => 'Lax'],
      $fixture->attributes()
    );
  }

  #[@test]
  public function in_path() {
    $fixture= new Cookies();
    $this->assertEquals('/sub', $fixture->path('/sub')->attributes()['path']);
  }

  #[@test]
  public function under_domain() {
    $fixture= new Cookies();
    $this->assertEquals('example.org', $fixture->domain('example.org')->attributes()['domain']);
  }

  #[@test]
  public function same_site() {
    $fixture= new Cookies();
    $this->assertEquals('Strict', $fixture->sameSite('Strict')->attributes()['sameSite']);
  }

  #[@test]
  public function secure() {
    $fixture= new Cookies();
    $this->assertTrue($fixture->insecure(false)->attributes()['secure']);
  }

  #[@test]
  public function insecure() {
    $fixture= new Cookies();
    $this->assertFalse($fixture->insecure(true)->attributes()['secure']);
  }

  #[@test]
  public function accessible() {
    $fixture= new Cookies();
    $this->assertFalse($fixture->accessible(true)->attributes()['httpOnly']);
  }

  #[@test]
  public function http_only() {
    $fixture= new Cookies();
    $this->assertTrue($fixture->accessible(false)->attributes()['httpOnly']);
  }
}