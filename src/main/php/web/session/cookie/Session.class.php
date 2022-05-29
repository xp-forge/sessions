<?php namespace web\session\cookie;

use web\session\{ISession, SessionInvalid};

/**
 * A session stored in a cookie
 *
 * @see   web.session.CookieBased
 */
class Session implements ISession {
  private $sessions, $values, $expire;
  private $id= null;
  private $modified= false;

  /**
   * Creates a new cookie-based session
   *
   * @param  web.session.Sessions $sessions
   * @param  [:var] $values
   * @param  int $expire
   */
  public function __construct($sessions, $values, $expire) {
    $this->sessions= $sessions;
    $this->values= $values;
    $this->expire= $expire;
  }

  /** @return string */
  public function id() { return $this->id ?? $this->id= $this->sessions->serialize($this->values, $this->expire); }

  /** @return bool */
  public function valid() { return time() < $this->expire; }

  /** @return void */
  public function destroy() {
    $this->expire= time() - 1;
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
      throw new SessionInvalid($this->id());
    }

    $this->values[$name]= [$value];
    $this->modified= true;
    $this->id= null;
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
      throw new SessionInvalid($this->id());
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
      throw new SessionInvalid($this->id());
    }

    if (!isset($this->values[$name])) return false;
    unset($this->values[$name]);
    $this->modified= true;
    $this->id= null;
    return true;
  }

  /**
   * Closes this session
   *
   * @return void
   */
  public function close() {
    // NOOP
  }

  /**
   * Transmits this session to the response
   *
   * @param  web.Response $response
   * @return void
   */
  public function transmit($response) {
    if (time() >= $this->expire) {
      $this->sessions->detach($this, $response);
    } else if ($this->modified) {
      $this->sessions->attach($this, $response);
      $this->modified= false;
    }
    $this->close();
  }
}