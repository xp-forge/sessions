<?php namespace web\session\unittest;

use io\Folder;
use lang\{Environment, IllegalArgumentException};
use test\Assert;
use test\{AfterClass, BeforeClass, Expect, Test};
use web\session\InFileSystem;

class InFileSystemTest extends SessionsTest {
  private static $dir;

  #[BeforeClass]
  public static function createSessionDir() {
    self::$dir= new Folder(Environment::tempDir(), uniqid());
    self::$dir->exists() || self::$dir->create(0777);
  }

  #[AfterClass]
  public static function removeSessionDir() {
    self::$dir->exists() && self::$dir->unlink();
  }

  /** @return web.session.Sessions */
  protected function fixture() { return new InFileSystem(self::$dir); }

  #[Test]
  public function can_create_without_argument() {
    new InFileSystem();
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function raises_error_when_non_existant_directory_is_given() {
    new InFileSystem('@does-not-exist@');
  }

  #[Test]
  public function session_identifiers_consist_of_32_lowercase_hex_digits() {
    $sessions= $this->fixture();
    $id= $sessions->create()->id();
    Assert::true((bool)preg_match('/^[a-f0-9]{32}$/i', $id), $id);
  }
}