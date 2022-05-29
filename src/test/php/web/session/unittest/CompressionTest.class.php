<?php namespace web\session\unittest;

use unittest\{Assert, Expect, Test, Values};
use web\session\cookie\Compression;

class CompressionTest {

  /** @return iterable */
  private function strings() {
    yield [''];
    yield ['AAA'];
    yield ['test'];
    yield ['Hello World'];
    yield ['{"user":"test","id":6100,"node_id":"MDQ6VXNlcjY5Njc0Mg==","root":false}'];
  }

  /** @return iterable */
  private function files() {
    yield [__FILE__];
    yield [PHP_BINARY];
  }

  #[Test, Values('strings')]
  public function short_strings_not_worthwhile($fixture) {
    Assert::false((new Compression())->worthwhile($fixture));
  }

  #[Test]
  public function worthwhile_with_string() {
    Assert::true((new Compression())->worthwhile(str_repeat('*', 258)));
  }

  #[Test]
  public function worthwhile_with_length() {
    Assert::true((new Compression())->worthwhile(258));
  }

  #[Test, Values('strings')]
  public function string_roundtrip($fixture) {
    $c= new Compression();
    Assert::equals($fixture, $c->decompress($c->compress($fixture)));
  }

  #[Test, Values('files')]
  public function file_roundtrip($source) {
    $fixture= file_get_contents($source);
    $c= new Compression();
    Assert::equals($fixture, $c->decompress($c->compress($fixture)));
  }

  #[Test, Values('files')]
  public function compressed_is_smaller_than($source) {
    $fixture= file_get_contents($source);
    $compressed= (new Compression())->compress($fixture);
    Assert::true(strlen($compressed) < strlen($fixture));
  }
}