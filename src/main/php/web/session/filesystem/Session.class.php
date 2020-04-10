<?php namespace web\session\filesystem;

use io\File;
use web\session\{ISession, SessionInvalid};

/**
 * A session stored in the filesystem
 *
 * @see   xp://web.session.InFileSystem
 */
class Session implements ISession {
  private $sessions, $new, $file, $eol;
  private $values= null;
  private $modifications= [];

  /**
   * Creates a new file-based session
   *
   * @param  web.session.Sessions $sessions
   * @param  string|io.File $file
   * @param  bool $new
   * @param  int eol
   */
  public function __construct($sessions, $file, $new, $eol) {
    $this->sessions= $sessions;
    $this->file= $file instanceof File ? $file : new File($file);
    $this->new= $new;
    $this->eol= $eol;

    if ($new) {
      $file->touch();
      $this->values= [];
    }
  }

  /** @return string */
  public function id() { return str_replace('sess_', '', $this->file->getFileName()); }

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
      throw new SessionInvalid($this->id());
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
    $this->eol= time() - 1;
    $this->new= false;
    $this->file->unlink();
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
    return isset($this->values[$name]) ? $this->values[$name][0] : $default;
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

  /**
   * Transmits this session to the response
   *
   * @param  web.Response $response
   * @return void
   */
  public function transmit($response) {
    if ($this->new) {
      $this->sessions->attach($this, $response);
      $this->new= false;
      // Fall through, writing session data
    } else if (time() >= $this->eol) {
      $this->sessions->detach($this, $response);
      return;
    }

    $this->close();
  }
}