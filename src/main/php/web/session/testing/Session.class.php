<?php namespace web\session\testing;

use web\session\ISession;

/**
 * A testing session
 *
 * @see   xp://web.session.ForTesting
 */
class Session implements ISession {
  private $id, $eol;
  private $values= [];

  /**
   * Creates a new in-memory session
   *
   * @param  string $id
   * @param  int $eol
   */
  public function __construct($id, $eol) {
    $this->id= $id;
    $this->eol= $eol;
  }

  /** @return string */
  public function id() { return $this->id; }

  /** @return bool */
  public function valid() { return time() < $this->eol; }

  /** @return void */
  public function close() { }

  /** @return void */
  public function destroy() {
    $this->eol= time() - 1;
    $this->values= [];
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
    if (time() >= $this->eol) {
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
    if (time() >= $this->eol) {
      throw new SessionInvalid($this->id);
    }
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
    if (time() >= $this->eol) {
      throw new SessionInvalid($this->id);
    }
    unset($this->values[$name]);
  }
}