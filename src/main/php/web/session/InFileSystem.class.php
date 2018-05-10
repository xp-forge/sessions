<?php namespace web\session;

use web\session\filesystem\Session;
use io\Folder;
use io\File;
use lang\Environment;
use util\Random;
use lang\IllegalStateException;

/**
 * Session factory that creates sessions in the local filesystem
 *
 * @test  xp://web.session.unittest.InFileSystemTest
 */
class InFileSystem extends Sessions {
  private $path, $random;

  /** @param io.Folder|io.Path|string $path */
  public function __construct($path= null) {
    if (null === $path) {
      $this->path= new Folder(Environment::tempDir());
    } else if ($path instanceof Folder) {
      $this->path= $path;
    } else {
      $this->path= new Folder($path);
    }
    $this->random= new Random();
  }

  /**
   * Creates a session
   *
   * @return web.session.Session
   */
  public function create() {
    $buffer= bin2hex($this->random->bytes(32));   // 64 bytes
    $offset= 0;

    do {
      $uri= $this->path->getURI().'sess_'.substr($buffer, $offset, 32);
      if (!file_exists($uri)) {
        touch($uri);
        return new Session(new File($uri), time() + $this->duration);
      }
    } while ($offset++ < 32);

    throw new IllegalStateException('Cannot create session. Out of randoms?');
  }

  /**
   * Locates an existing session; returns NULL if there is no such session.
   *
   * @param  string $id
   * @return web.session.Session
   */
  public function locate($id) {
    $f= new File($this->path->getURI(), $id);
    if ($f->exists()) {
      $age= time() - $f->createdAt();
      return new Session($f, time() + ($this->duration - $age));
    }
    return null;
  }
}