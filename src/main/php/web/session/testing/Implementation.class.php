<?php namespace web\session\testing;

use web\session\{Session, SessionInvalid};

/**
 * Testing session implementation
 *
 * @see   web.session.ForTesting
 * @test  web.session.unittest.ForTestingTest
 */
class Implementation extends Session {
  private $modified, $token;
  private $values= [];

  /**
   * Creates a new in-memory session
   *
   * @param  web.session.Sessions $sessions
   * @param  int $expire
   * @param  string $token
   * @param  bool $modified
   */
  public function __construct($sessions, $expire, $token, $modified) {
    parent::__construct($sessions, $expire);
    $this->token= $token;
    $this->modified= $modified;
  }

  /** @return string */
  public function token() { return $this->token; }

  /** @return bool */
  public function modified() { return $this->modified; }

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
    $this->expire= time() - 1;
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
    if (time() >= $this->expire) {
      throw new SessionInvalid($this->token);
    }
    $this->values[$name]= [$value];
    $this->modified= true;
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
      $this->modified= true;
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
    $this->modified= false;
  }
}