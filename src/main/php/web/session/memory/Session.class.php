<?php namespace web\session\memory;

use web\session\ISession;

/**
 * An in-memory session
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
   */
  public function register($name, $value) {
    $this->values[$name]= [$value];
  }

  /**
   * Retrieves a value - reading it from the session
   *
   * @param  string $name
   * @param  var $default
   * @return var
   */
  public function value($name, $default= null) {
    return isset($this->values[$name]) ? $this->values[$name][0] : $default;
  }

  /**
   * Removes a value - deleting it from the session
   *
   * @param  string $name
   * @return void
   */
  public function remove($name) {
    unset($this->values[$name]);
  }
}