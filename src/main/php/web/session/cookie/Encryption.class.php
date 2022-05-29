<?php namespace web\session\cookie;

use lang\{IllegalArgumentException, IllegalStateException};
use util\Secret;

abstract class Encryption {
  protected $key;

  /**
   * Creates a new instance with a given key
   * 
   * @throws lang.IllegalArgumentException
   */
  public function __construct(Secret $key) {
    if (strlen($key->reveal()) < 32) {
      throw new IllegalArgumentException('Key must be 32 bytes in length');
    }
    $this->key= $key;
  }

  /**
   * Returns a unique identifier for the given format consisting
   * of one uppercase character.
   *
   * @return string
   */
  public abstract function id();

  /**
   * Encrypt given plain text input
   *
   * @param  string $input
   * @return string
   */
  public abstract function encrypt($input);

  /**
   * Decrypt given ciphertext
   *
   * @param  string $input
   * @return string
   * @throws lang.FormatException
   */
  public abstract function decrypt($input);

  /**
   * Instantiates a new encryption implementation using the given key.
   * If no implementations are availale, raises an exception.
   *
   * @param  util.Secret $key
   * @return self
   * @throws lang.IllegalStateException
   */
  public static function using(Secret $key) {
    foreach (self::available($key) as $impl) {
      return $impl;
    }

    throw new IllegalStateException('No encryption implementations available');
  }

  /**
   * Returns all available format implementations. Prefers *Sodium*,
   * then checks to see if *OpenSSL* is available.
   *
   * @param  util.Secret $key
   * @return iterable
   */
  public static function available(Secret $key) {
    extension_loaded('sodium') && yield new Sodium($key);
    extension_loaded('openssl') && yield new OpenSSL($key);
  }
}