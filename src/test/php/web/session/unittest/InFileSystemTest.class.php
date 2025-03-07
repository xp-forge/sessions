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
    Assert::matches('/^[a-f0-9]{32}$/i', $id);
  }

  #[Test]
  public function issue_15_extra_data_during_unserialize() {
    $sessions= $this->fixture();

    // Create session and register value
    $a= $sessions->create();
    $a->register('name', 'initial');
    $a->close();

    // Overwrite initial value with a shorter one, this should truncate
    $b= $sessions->open($a->id());
    $b->register('name', 'test');
    $b->close();

    // Modify session again, should not trigger the "extra data" warning
    $c= $sessions->open($a->id());
    $c->register('name', 'irrelevant');
    $c->close();
  }
}