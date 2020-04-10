<?php namespace web\session;

use io\{File, Folder};
use lang\{Environment, IllegalArgumentException, IllegalStateException};
use util\Random;
use web\session\filesystem\Session;

/**
 * Session factory that creates sessions in the local filesystem
 *
 * @test  xp://web.session.unittest.InFileSystemTest
 */
class InFileSystem extends Sessions {
  private $folder, $prefix, $random;

  /**
   * Creates a new filesystem-based factory
   *
   * @param  io.Folder|io.Path|string $path
   * @param  string $prefix Prefix all files, defaults to "sess_"
   * @throws lang.IllegalArgumentException if the path does not exist or is not writable
   */
  public function __construct($path= null, $prefix= 'sess_') {
    if (null === $path) {
      $this->folder= new Folder(Environment::tempDir());
    } else if ($path instanceof Folder) {
      $this->folder= $path;
    } else {
      $this->folder= new Folder($path);
    }

    if (!is_writable($this->folder->getURI())) {
      throw new IllegalArgumentException('Path '.$this->folder->getURI().' is not writable');
    }

    $this->prefix= $prefix;
    $this->random= new Random();
  }

  /** @return int */
  public function gc() {
    $expiry= time() - $this->duration;
    $deleted= 0;
    foreach (glob($this->folder->getURI().$this->prefix.'*') as $file) {
      if (filectime($file) >= $expiry) continue;

      unlink($file);
      $deleted++;
    }
    return $deleted;
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
      $f= new File($this->folder, $this->prefix.substr($buffer, $offset, 32));
      if (!$f->exists()) {
        $this->gc();
        return new Session($this, $f, true, time() + $this->duration);
      }
    } while ($offset++ < 32);

    throw new IllegalStateException('Cannot create session. Out of randoms?');
  }

  /**
   * Opens an existing and valid session. 
   *
   * @param  string $id
   * @return web.session.ISession
   */
  public function open($id) {
    $f= new File($this->folder->getURI(), $this->prefix.$id);
    if ($f->exists()) {
      $created= $f->createdAt();
      if (time() - $created < $this->duration) {
        return new Session($this, $f, false, $created + $this->duration);
      }
      $f->unlink();
    }
    return null;
  }
}