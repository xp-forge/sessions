<?php namespace web\session\cookie;

use lang\FormatException;

/**
 * Encrypts cookies using sodium functions
 *
 * @ext   sodium
 * @test  web.session.unittest.CookieFormatTest
 */
class Sodium extends Encryption {

  public function id() { return 'S'; }

  public function encrypt($input) {
    $nonce= random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    return $nonce.sodium_crypto_secretbox($input, $nonce, $this->key->reveal());
  }

  public function decrypt($input) {
    if (false === ($result= sodium_crypto_secretbox_open(
      substr($input, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
      substr($input, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
      $this->key->reveal()
    ))) {
      throw new FormatException('Ciphertext was tampered with!');
    }

    return $result;
  }
}