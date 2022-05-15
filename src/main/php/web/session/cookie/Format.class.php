<?php namespace web\session\cookie;

use lang\{Enum, FormatException};

abstract class Format extends Enum {
  public static $SODIUM, $OPENSSL;

  static function __static() {
    self::$SODIUM= new class(0, 'SODIUM') extends Format {
      static function __static() { }

      public function id() { return 's'; }

      public function encrypt($input, $key) {
        $nonce= random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        return $nonce.sodium_crypto_secretbox($input, $nonce, $key->reveal());
      }

      public function decrypt($input, $key) {
        if (false === ($result= sodium_crypto_secretbox_open(
          substr($input, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
          substr($input, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
          $key->reveal()
        ))) {
          throw new FormatException('Ciphertext was tampered with!');
        }

        return $result;
      }
    };
    self::$OPENSSL= new class(1, 'OPENSSL') extends Format {
      const METHOD = 'AES-256-CBC';
      const IV_LEN = 16;
      
      static function __static() { }

      public function id() { return 'o'; }

      public function encrypt($input, $key) {
        $iv= openssl_random_pseudo_bytes(self::IV_LEN);
        $cipher= openssl_encrypt($input, self::METHOD, $key->reveal(), OPENSSL_RAW_DATA, $iv);
        return $iv.hash_hmac('sha256', $cipher.$iv, $key->reveal(), true).$cipher;
      }

      public function decrypt($input, $key) {
        $iv= substr($input, 0, self::IV_LEN);
        $cipher= substr($input, self::IV_LEN + 32); // 32 = length of sha256 hash
        if (!hash_equals(hash_hmac('sha256', $cipher.$iv, $key->reveal(), true), substr($input, self::IV_LEN, 32))) {
          throw new FormatException('Ciphertext was tampered with!');
        }

        return openssl_decrypt($cipher, self::METHOD, $key->reveal(), OPENSSL_RAW_DATA, $iv);
      }
    };
  }

  /**
   * Returns a unique one-character identifier for the given format
   *
   * @return string
   */
  public abstract function id();


  /**
   * Encrypt given plain text input
   *
   * @param  string $input
   * @param  util.Secret $key
   * @return string
   */
  public abstract function encrypt($input, $key);

  /**
   * Decrypt given ciphertext
   *
   * @param  string $input
   * @param  util.Secret $key
   * @return string
   * @throws lang.FormatException
   */
  public abstract function decrypt($input, $key);

  /**
   * Returns all available format implementations
   *
   * @return self[]
   */
  public static function available() {
    $r= [];
    extension_loaded('sodium') && $r[]= self::$SODIUM;
    extension_loaded('openssl') && $r[]= self::$OPENSSL;
    return $r;
  }
}