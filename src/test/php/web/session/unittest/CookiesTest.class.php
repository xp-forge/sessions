<?php namespace web\session\unittest;

use test\Assert;
use test\{Test, TestCase};
use web\session\Cookies;

class CookiesTest {

  #[Test]
  public function can_create() {
    new Cookies();
  }

  #[Test]
  public function defaults() {
    $fixture= new Cookies();
    Assert::equals(
      ['path' => '/', 'secure' => true, 'domain' => null, 'httpOnly' => true, 'sameSite' => 'Lax'],
      $fixture->attributes()
    );
  }

  #[Test]
  public function in_path() {
    $fixture= new Cookies();
    Assert::equals('/sub', $fixture->path('/sub')->attributes()['path']);
  }

  #[Test]
  public function under_domain() {
    $fixture= new Cookies();
    Assert::equals('example.org', $fixture->domain('example.org')->attributes()['domain']);
  }

  #[Test]
  public function same_site() {
    $fixture= new Cookies();
    Assert::equals('Strict', $fixture->sameSite('Strict')->attributes()['sameSite']);
  }

  #[Test]
  public function secure() {
    $fixture= new Cookies();
    Assert::true($fixture->insecure(false)->attributes()['secure']);
  }

  #[Test]
  public function insecure() {
    $fixture= new Cookies();
    Assert::false($fixture->insecure(true)->attributes()['secure']);
  }

  #[Test]
  public function accessible() {
    $fixture= new Cookies();
    Assert::false($fixture->accessible(true)->attributes()['httpOnly']);
  }

  #[Test]
  public function http_only() {
    $fixture= new Cookies();
    Assert::true($fixture->accessible(false)->attributes()['httpOnly']);
  }
}