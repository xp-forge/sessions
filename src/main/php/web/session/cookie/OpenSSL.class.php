<?php namespace web\session\cookie;

use lang\FormatException;

/**
 * Encrypts cookies using openssl functions
 *
 * @ext   openssl
 * @see   https://www.daemonology.net/blog/2009-06-24-encrypt-then-mac.html
 * @test  web.session.unittest.CookieFormatTest
 */
class OpenSSL extends Encryption {
  const METHOD = 'AES-256-CBC';
  const IV_LEN = 16;

  public function id() { return 'O'; }

  public function encrypt($input) {
    $iv= openssl_random_pseudo_bytes(self::IV_LEN);
    $cipher= openssl_encrypt($input, self::METHOD, $this->key->reveal(), OPENSSL_RAW_DATA, $iv);
    return $iv.hash_hmac('sha256', $cipher.$iv, $this->key->reveal(), true).$cipher;
  }

  public function decrypt($input) {
    $iv= substr($input, 0, self::IV_LEN);
    $cipher= substr($input, self::IV_LEN + 32); // 32 = length of sha256 hash
    if (!hash_equals(hash_hmac('sha256', $cipher.$iv, $this->key->reveal(), true), substr($input, self::IV_LEN, 32))) {
      throw new FormatException('Ciphertext was tampered with!');
    }

    return openssl_decrypt($cipher, self::METHOD, $this->key->reveal(), OPENSSL_RAW_DATA, $iv);
  }
}