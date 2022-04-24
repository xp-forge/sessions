<?php namespace web\session\cookie;

use io\File;
use web\session\{ISession, SessionInvalid};

/**
 * A session stored in a cookie
 *
 * @see   xp://web.session.CookieBased
 */
class Session implements ISession {
  private $sessions;
  public $claims;
  private $modified= false;

  /**
   * Creates a new cookie-based session
   *
   * @param  web.session.Sessions $sessions
   * @param  [:var] $claims
   */
  public function __construct($sessions, $claims) {
    $this->sessions= $sessions;
    $this->claims= $claims;
  }

  /** @return string */
  public function id() { return $this->sessions->serialize($this->claims); }

  /** @return bool */
  public function valid() { return time() < $this->claims['exp']; }

  /** @return void */
  public function destroy() {
    $this->claims['exp']= time() - 1;
  }

  /**
   * Returns all session keys
   *
   * @return string[]
   */
  public function keys() {
    return array_keys($this->claims['val']);
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
    if (time() >= $this->claims['exp']) {
      throw new SessionInvalid($this->id());
    }

    $this->claims['val'][$name]= [$value];
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
    if (time() >= $this->claims['exp']) {
      throw new SessionInvalid($this->id());
    }

    return $this->claims['val'][$name][0] ?? $default;
  }

  /**
   * Removes a value - deleting it from the session
   *
   * @param  string $name
   * @return bool
   * @throws web.session.SessionInvalid
   */
  public function remove($name) {
    if (time() >= $this->claims['exp']) {
      throw new SessionInvalid($this->id());
    }

    if (!isset($this->claims['val'][$name])) return false;
    unset($this->claims['val'][$name]);
    return $this->modified= true;
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
    if (time() >= $this->claims['exp']) {
      $this->sessions->detach($this, $response);
    } else if ($this->modified) {
      $this->sessions->attach($this, $response);
      $this->modified= false;
    }
    $this->close();
  }
}