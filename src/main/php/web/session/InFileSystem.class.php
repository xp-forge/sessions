<?php namespace web\session;

use io\File;
use io\Folder;
use lang\Environment;
use lang\IllegalArgumentException;
use lang\IllegalStateException;
use util\Random;
use web\session\filesystem\Session;

/**
 * Session factory that creates sessions in the local filesystem
 *
 * @test  xp://web.session.unittest.InFileSystemTest
 */
class InFileSystem extends Sessions {
  private $path, $prefix, $random;

  /**
   * Creates a new filesystem-based factory
   *
   * @param io.Folder|io.Path|string $path
   * @param  string $prefix Prefix all files, defaults to "sess_"
   * @throws lang.IllegalArgumentException if the path does not exist or is not writable
   */
  public function __construct($path= null, $prefix= 'sess_') {
    if (null === $path) {
      $this->path= new Folder(Environment::tempDir());
    } else if ($path instanceof Folder) {
      $this->path= $path;
    } else {
      $this->path= new Folder($path);
    }

    if (!is_writable($this->path->getURI())) {
      throw new IllegalArgumentException('Path '.$this->path->getURI().' is not writable');
    }

    $this->prefix= $prefix;
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
      $f= new File($this->path, $this->prefix.substr($buffer, $offset, 32));
      if (!$f->exists() && $f->touch()) {
        return new Session($f, time() + $this->duration);
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
    $f= new File($this->path->getURI(), $this->prefix.$id);
    if ($f->exists()) {
      $age= time() - $f->createdAt();
      return new Session($f, time() + ($this->duration - $age));
    }
    return null;
  }
}