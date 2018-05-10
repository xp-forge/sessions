<?php namespace web\session;

use web\session\filesystem\Session;
use io\Folder;
use io\File;
use lang\Environment;

/**
 * Session factory that creates sessions in the local filesystem
 *
 * @test  xp://web.session.unittest.InFileSystemTest
 */
class InFileSystem extends Sessions {
  private $path;

  /** @param io.Folder|io.Path|string $path */
  public function __construct($path= null) {
    if (null === $path) {
      $this->path= new Folder(Environment::tempDir());
    } else if ($path instanceof Folder) {
      $this->path= $path;
    } else {
      $this->path= new Folder($path);
    }
  }

  /**
   * Creates a session
   *
   * @return web.session.Session
   */
  public function create() {
    return new Session(new File(tempnam($this->path->getURI(), 'session')), time() + $this->duration);
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