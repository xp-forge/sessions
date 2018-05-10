<?php namespace web\session\unittest;

use unittest\TestCase;
use web\session\InFileSystem;
use web\session\ISession;
use lang\Environment;
use io\Folder;

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
  public function create() {
    $sessions= new InFileSystem();
    $this->assertInstanceOf(ISession::class, $sessions->create());
  }

  #[@test]
  public function read_write() {
    $session= (new InFileSystem())->create();
    try {
      $session->register('name', 'value');
      $this->assertEquals('value', $session->value('name'));
    } finally {
      $session->close();
    }
  }

  #[@test]
  public function read_non_existant() {
    $session= (new InFileSystem())->create();
    try {
      $this->assertNull($session->value('name'));
    } finally {
      $session->close();
    }
  }

  #[@test]
  public function read_non_existant_returns_default() {
    $session= (new InFileSystem())->create();
    try {
      $this->assertEquals('Default value', $session->value('name', 'Default value'));
    } finally {
      $session->close();
    }
  }

  #[@test]
  public function remove() {
    $session= (new InFileSystem())->create();
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
    $sessions= new InFileSystem();

    $session= $sessions->create();
    $session->register('name', 'value');
    $session->close();

    $session= $sessions->open($session->id());
    $value= $session->value('name');
    $session->close();

    $this->assertEquals('value', $value);
  }
}