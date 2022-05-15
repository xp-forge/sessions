<?php namespace web\session\unittest;

use lang\FormatException;
use unittest\{Test, TestCase};
use util\Secret;
use web\session\cookie\Format;

class CookieFormatTest extends TestCase {
  private $key;

  /** @return void */
  public function setUp() {
    $this->key= new Secret('tlw3/ELaLfu3kmpzQJ0MDCdRG2b8Le+X'); // 32 bytes
  }

  /** @return iterable */
  private function available() {
    foreach (Format::available() as $format) {
      yield [$format];
    }
  }

  #[Test, Values('available')]
  public function empty_roundtrip($impl) {
    $this->assertEquals('', $impl->decrypt($impl->encrypt('', $this->key), $this->key));
  }

  #[Test, Values('available')]
  public function test_roundtrip($impl) {
    $this->assertEquals('Test', $impl->decrypt($impl->encrypt('Test', $this->key), $this->key));
  }

  #[Test, Values('available')]
  public function roundtrip_with_64_kbytes($impl) {
    $payload= str_repeat('A*', 32768);
    $this->assertEquals($payload, $impl->decrypt($impl->encrypt($payload, $this->key), $this->key));
  }

  #[Test, Expect(FormatException::class), Values('available')]
  public function detects_payload_being_tampered_with($impl) {
    $encrypted= $impl->encrypt('Test', $this->key);
    $encrypted[0]= chr(ord($encrypted[0]) + 1);

    $impl->decrypt($encrypted, $this->key);
  }

  #[Test, Expect(FormatException::class), Values('available')]
  public function detects_payload_being_shortened($impl) {
    $encrypted= $impl->encrypt('Test', $this->key);
    $encrypted= substr($encrypted, 0,  -1);

    $impl->decrypt($encrypted, $this->key);
  }

  #[Test, Expect(FormatException::class), Values('available')]
  public function detects_payload_being_appended_to($impl) {
    $encrypted= $impl->encrypt('Test', $this->key);
    $encrypted.= "\0";

    $impl->decrypt($encrypted, $this->key);
  }
}