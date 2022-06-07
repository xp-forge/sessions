<?php namespace web\session\filesystem;

use io\File;
use web\session\{Session, SessionInvalid};

/**
 * A session stored in the filesystem
 *
 * @see   web.session.InFileSystem
 * @test  web.session.unittest.InFileSystemTest
 */
class Implementation extends Session {
  private $file, $attach, $values;
  private $modifications= [];

  /**
   * Creates a new file-based session
   *
   * @param  web.session.Sessions $sessions
   * @param  int $expire
   * @param  string|io.File $file
   * @param  bool $new
   */
  public function __construct($sessions, $expire, $file, $new) {
    parent::__construct($sessions, $expire);
    $this->file= $file instanceof File ? $file : new File($file);

    // Only attach sessions to the request during creation.
    if ($new) {
      $this->attach= true;
      $this->values= [];
    } else {
      $this->attach= false;
      $this->values= null;
    }
  }

  /** @return string */
  public function token() { return substr($this->file->getFileName(), strlen($this->sessions->prefix)); }

  /** @return bool */
  public function valid() { return parent::valid() && $this->file->exists(); }

  /** @return bool */
  public function attach() { return $this->attach; }

  /** @return int */
  private function size() {
    clearstatcache(true, $this->file->getURI());
    return $this->file->size();
  }

  /**
   * Open and read underlying file; lazily initalized and cached.
   *
   * @return void
   * @throws web.session.SessionInvalid
   */
  private function open() {
    if (time() >= $this->expire) {
      throw new SessionInvalid($this->token());
    } else if (null !== $this->values) {
      return;
    }

    $this->file->open(File::READ);
    $this->file->lockShared();
    $this->values= unserialize($this->file->read($this->size()));
    $this->file->unLock();
    $this->file->close();
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
  public function destroy() {
    $this->file->unlink();
    parent::destroy();
  }

  /**
   * Returns all session keys
   *
   * @return string[]
   */
  public function keys() {
    $this->open();
    return array_keys($this->values);
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
    return $this->values[$name][0] ?? $default;
  }

  /**
   * Removes a value - deleting it from the session
   *
   * @param  string $name
   * @return bool
   * @throws web.session.SessionInvalid
   */
  public function remove($name) {
    $this->open();

    if (isset($this->values[$name])) {
      $this->modify($name, function() use($name) { unset($this->values[$name]); });
      return true;
    } else {
      return false;
    } 
  }

  /**
   * Closes this session
   *
   * @return void
   */
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
}