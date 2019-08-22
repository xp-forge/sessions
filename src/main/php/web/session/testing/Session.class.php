<?php namespace web\session\testing;

use web\session\ISession;
use web\session\SessionInvalid;

/**
 * A testing session
 *
 * @see   xp://web.session.ForTesting
 */
class Session implements ISession {
  private $sessions, $new, $id, $eol;
  private $values= [];

  /**
   * Creates a new in-memory session
   *
   * @param  web.session.Sessions $sessions
   * @param  string $id
   * @param  bool $new
   * @param  int $eol
   */
  public function __construct($sessions, $id, $new, $eol) {
    $this->sessions= $sessions;
    $this->id= $id;
    $this->new= $new;
    $this->eol= $eol;
  }

  /** @return string */
  public function id() { return $this->id; }

  /** @return bool */
  public function valid() { return time() < $this->eol; }

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
    $this->eol= time() - 1;
    $this->new= false;
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
   * @return bool
   * @throws web.session.SessionInvalid
   */
  public function remove($name) {
    if (time() >= $this->eol) {
      throw new SessionInvalid($this->id);
    }

    if (isset($this->values[$name])) {
      unset($this->values[$name]);
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
    // NOOP
  }

  /**
   * Closes and transmits this session to the response
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