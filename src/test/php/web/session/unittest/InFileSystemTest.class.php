<?php namespace web\session\unittest;

use unittest\TestCase;
use web\session\InFileSystem;
use web\session\ISession;
use lang\Environment;
use io\Folder;
use lang\IllegalArgumentException;

class InFileSystemTest extends TestCase {
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

  #[@test]
  public function can_create() {
    new InFileSystem();
  }

  #[@test]
  public function can_create_with_dir() {
    new InFileSystem(self::$dir);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function raises_error_when_non_existant_directory_is_given() {
    new InFileSystem('@does-not-exist@');
  }

  #[@test]
  public function create() {
    $sessions= new InFileSystem(self::$dir);
    $this->assertInstanceOf(ISession::class, $sessions->create());
  }

  #[@test]
  public function session_identifiers_consist_of_32_lowercase_hex_digits() {
    $sessions= new InFileSystem(self::$dir);
    $id= $sessions->create()->id();
    $this->assertTrue((bool)preg_match('/^[a-f0-9]{32}$/i', $id), $id);
  }

  #[@test]
  public function read_write() {
    $session= (new InFileSystem(self::$dir))->create();
    try {
      $session->register('name', 'value');
      $this->assertEquals('value', $session->value('name'));
    } finally {
      $session->close();
    }
  }

  #[@test]
  public function read_non_existant() {
    $session= (new InFileSystem(self::$dir))->create();
    try {
      $this->assertNull($session->value('name'));
    } finally {
      $session->close();
    }
  }

  #[@test]
  public function read_non_existant_returns_default() {
    $session= (new InFileSystem(self::$dir))->create();
    try {
      $this->assertEquals('Default value', $session->value('name', 'Default value'));
    } finally {
      $session->close();
    }
  }

  #[@test]
  public function remove() {
    $session= (new InFileSystem(self::$dir))->create();
    try {
      $session->register('name', 'value');
      $session->remove('name');
      $this->assertNull($session->value('name'));
    } finally {
      $session->close();
    }
  }

  #[@test]
  public function read_write_with_two_session_instances() {
    $sessions= new InFileSystem(self::$dir);

    $session= $sessions->create();
    $session->register('name', 'value');
    $session->close();

    $session= $sessions->open($session->id());
    $value= $session->value('name');
    $session->close();

    $this->assertEquals('value', $value);
  }

  #[@test]
  public function destroy() {
    $session= (new InFileSystem(self::$dir))->create();
    try {
      $session->register('name', 'value');
      $session->destroy();
      $this->assertFalse($session->valid());
    } finally {
      $session->close();
    }
  }
}