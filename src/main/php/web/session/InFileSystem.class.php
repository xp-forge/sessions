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

  /** @return int */
  public function gc() {
    $expiry= time() - $this->duration;
    $deleted= 0;
    foreach (glob($this->path->getURI().$this->prefix.'*') as $file) {
      if (filectime($file) >= $expiry) continue;

      unlink($file);
      $deleted++;
    }
    return $deleted;
  }

  /**
   * Creates a session
   *
   * @param  web.Response $response
   * @return web.session.Session
   */
  public function create($response) {
    $buffer= bin2hex($this->random->bytes(32));   // 64 bytes
    $offset= 0;

    do {
      $id= substr($buffer, $offset, 32);
      $f= new File($this->path, $this->prefix.$id);
      if (!$f->exists() && $f->touch()) {
        $this->gc();
        $this->transmit($response, $id);
        return new Session($f, time() + $this->duration);
      }
    } while ($offset++ < 32);

    throw new IllegalStateException('Cannot create session. Out of randoms?');
  }

  /**
   * Locates an existing session; returns NULL if there is no such session.
   *
   * @param  web.Request $request
   * @return web.session.Session
   */
  public function locate($request) {
    if ($id= $this->id($request)) {
      $f= new File($this->path->getURI(), $this->prefix.$id);
      if ($f->exists()) {
        $created= $f->createdAt();
        if (time() - $created < $this->duration) {
          return new Session($f, $created + $this->duration);
        }
        $f->unlink();
      }
    }
    return null;
  }
}