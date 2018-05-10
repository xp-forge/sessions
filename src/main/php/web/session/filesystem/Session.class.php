<?php namespace web\session\filesystem;

use web\session\ISession;
use io\File;

/**
 * A session stored in the filesystem
 *
 * @see   xp://web.session.InFileSystem
 */
class Session implements ISession {
  private $file, $eol;
  private $values= null;
  private $modifications= [];

  /**
   * Creates a new file-based session
   *
   * @param  string|io.File $file
   * @param  int eol
   */
  public function __construct($file, $eol) {
    $this->file= $file instanceof File ? $file : new File($file);
    $this->eol= $eol;
  }

  /** @return string */
  public function id() { return $this->file->getFileName(); }

  /** @return bool */
  public function valid() { return time() < $this->eol; }

  /** @return int */
  private function size() {
    clearstatcache(false, $this->file->getURI());
    return $this->file->size();
  }

  /**
   * Open and read underlying file; lazily initalized and cached.
   *
   * @return void
   * @throws web.session.SessionInvalid
   */
  private function open() {
    if (time() >= $this->eol) {
      throw new SessionInvalid($this->file->getFileName());
    } else if (null !== $this->values) {
      return;
    }

    if (0 === $size= $this->size()) {
      $this->values= [];
    } else {
      $this->file->open(File::READ);
      $this->file->lockShared();
      $this->values= unserialize($this->file->read($size));
      $this->file->unLock();
      $this->file->close();
    }
  }

  /**
   * Perform a modification
   * 
   * @param  string $name
   * @param  function(string): void $modification
   * @return void
   */
  private function modify($name, $modification) {
    $this->modifications[$name]= $modification;
    $modification($name);
  }

  /** @return void */
  public function close() {
    if (empty($this->modifications)) return;

    $this->file->open(File::READWRITE);
    $this->file->lockExclusive();

    // Read file to ensure we have the most current version of the data
    if (0 === $size= $this->size()) {
      $this->values= [];
    } else {
      $this->values= unserialize($this->file->read($size));
      $this->file->seek(0, SEEK_SET);
    }

    // Replay all modifications, then write back
    foreach ($this->modifications as $name => $modification) {
      $modification($name);
    }
    $this->modifications= [];
    $this->file->write(serialize($this->values));

    $this->file->unLock();
    $this->file->close();
  }

  /** @return void */
  public function destroy() {
    $this->eol= time() - 1;
    $this->file->unlink();
  }

  /**
   * Registers a value - writing it to the session
   *
   * @param  string $name
   * @param  var $value
   * @return void
   * @throws web.session.SessionInvalid
   */
  public function register($name, $value) {
    $this->open();
    $this->modify($name, function($name) use($value) { $this->values[$name]= [$value]; });
  }

  /**
   * Retrieves a value - reading it from the session
   *
   * @param  string $name
   * @param  var $default
   * @return var
   * @throws web.session.SessionInvalid
   */
  public function value($name, $default= null) {
    $this->open();
    return isset($this->values[$name]) ? $this->values[$name][0] : $default;
  }

  /**
   * Removes a value - deleting it from the session
   *
   * @param  string $name
   * @return void
   * @throws web.session.SessionInvalid
   */
  public function remove($name) {
    $this->open();
    $this->modify($name, function() use($name) { unset($this->values[$name]); });
  }
}