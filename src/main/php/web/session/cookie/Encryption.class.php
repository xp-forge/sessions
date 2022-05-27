<?php namespace web\session\cookie;

use lang\IllegalArgumentException;
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
   * Returns a unique one-character identifier for the given format
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
   * Returns all available format implementations
   *
   * @param  util.Secret $key
   * @return self[]
   */
  public static function available(Secret $key) {
    $r= [];
    extension_loaded('sodium') && $r[]= new Sodium($key);
    extension_loaded('openssl') && $r[]= new OpenSSL($key);
    return $r;
  }
}