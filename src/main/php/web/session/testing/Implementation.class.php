<?php namespace web\session\testing;

use web\session\{Session, SessionInvalid};

/**
 * Testing session implementation
 *
 * @see   web.session.ForTesting
 * @test  web.session.unittest.ForTestingTest
 */
class Implementation extends Session {
  private $attach, $token;
  private $values= [];

  /**
   * Creates a new in-memory session
   *
   * @param  web.session.Sessions $sessions
   * @param  int $expire
   * @param  string $token
   * @param  bool $attach
   */
  public function __construct($sessions, $expire, $token, $attach) {
    parent::__construct($sessions, $expire);
    $this->token= $token;
    $this->attach= $attach;
  }

  /** @return string */
  public function token() { return $this->token; }

  /** @return bool */
  public function attach() { return $this->attach; }

  /** @return self */
  public function open() {
    $this->attach= false;
    return $this;
  }

  /** @return void */
  public function destroy() {
    $this->values= null;
    parent::destroy();
  }

  /**
   * Returns all session keys
   *
   * @return string[]
   */
  public function keys() {
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
    if (time() >= $this->expire) {
      throw new SessionInvalid($this->token);
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
    if (time() >= $this->expire) {
      throw new SessionInvalid($this->token);
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
    if (time() >= $this->expire) {
      throw new SessionInvalid($this->token);
    }

    if (isset($this->values[$name])) {
      unset($this->values[$name]);
      return true;
    } else {
      return false;
    }
  }
}