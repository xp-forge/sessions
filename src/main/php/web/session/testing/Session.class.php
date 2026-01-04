<?php namespace web\session\testing;

use web\session\{Persistence, SessionInvalid};

/**
 * A testing session
 *
 * @see   web.session.ForTesting
 */
class Session extends Persistence {
  private $id;
  private $values= [];

  /**
   * Creates a new in-memory session
   *
   * @param  web.session.Sessions $sessions
   * @param  string $id
   * @param  bool $detached
   * @param  int $expires
   */
  public function __construct($sessions, $id, $detached, $expires) {
    parent::__construct($sessions, $detached, $expires);
    $this->id= $id;
  }

  /** @return string */
  public function id() { return $this->id; }

  /**
   * Returns all session keys
   *
   * @return string[]
   */
  public function keys() {
    return array_keys($this->values);
  }

  /** @return void */
  public function destroy() {
    $this->expires= time() - 1;
    $this->detached= false;
    $this->sessions->gc();
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
    if (time() >= $this->expires) {
      throw new SessionInvalid($this->id);
    }
    $this->values[$name]= [$value];
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
    if (time() >= $this->expires) {
      throw new SessionInvalid($this->id);
    }
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
    if (time() >= $this->expires) {
      throw new SessionInvalid($this->id);
    }

    if (isset($this->values[$name])) {
      unset($this->values[$name]);
      return true;
    } else {
      return false;
    }
  }
}