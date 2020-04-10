<?php namespace web\session\unittest;

use io\Folder;
use lang\{Environment, IllegalArgumentException};
use web\session\InFileSystem;

class InFileSystemTest extends SessionsTest {
  private static $dir;

  #[@beforeClass]
  public static function createSessionDir() {
    self::$dir= new Folder(Environment::tempDir(), uniqid());
    self::$dir->exists() || self::$dir->create(0777);
  }

  #[@afterClass]
  public static function removeSessionDir() {
    self::$dir->exists() && self::$dir->unlink();
  }

  /** @return web.session.Sessions */
  protected function fixture() { return new InFileSystem(self::$dir); }

  #[@test]
  public function can_create_without_argument() {
    new InFileSystem();
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function raises_error_when_non_existant_directory_is_given() {
    new InFileSystem('@does-not-exist@');
  }

  #[@test]
  public function session_identifiers_consist_of_32_lowercase_hex_digits() {
    $sessions= $this->fixture();
    $id= $sessions->create()->id();
    $this->assertTrue((bool)preg_match('/^[a-f0-9]{32}$/i', $id), $id);
  }
}