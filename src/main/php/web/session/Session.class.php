<?php namespace web\session;

use lang\Closeable;

abstract class Session implements Closeable {
  protected $sessions;

  /** @param web.session.Sessions $sessions */
  public function __construct($sessions) {
    $this->sessions= $sessions;
  }

  /**
   * Returns the session token
   *
   * @return string
   */
  public abstract function token();

  /**
   * Returns whether the session is valid
   *
   * @return bool
   */
  public abstract function valid();

  /**
   * Returns whether the session has been modified
   *
   * @return bool
   */
  public abstract function modified();

  /**
   * Destroys the session
   *
   * @return void
   */
  public abstract function destroy();

  /**
   * Returns all session keys
   *
   * @return string[]
   */
  public abstract function keys();

  /**
   * Registers a value - writing it to the session
   *
   * @param  string $name
   * @param  var $value
   * @return void
   * @throws web.session.SessionInvalid
   */
  public abstract function register($name, $value);

  /**
   * Retrieves a value - reading it from the session
   *
   * @param  string $name
   * @param  var $default
   * @return var
   * @throws web.session.SessionInvalid
   */
  public abstract function value($name, $default= null);

  /**
   * Removes a value - deleting it from the session
   *
   * @param  string $name
   * @return bool Whether the value previously existed
   * @throws web.session.SessionInvalid
   */
  public abstract function remove($name);

  /**
   * Closes this session and flushes data
   *
   * @return void
   */
  public abstract function close();

  /**
   * Transmits this session to the response. In case the session hasn't
   * been modified, do not send a superfluous `Set-Cookie` header.
   *
   * @param  web.Response $response
   * @return void
   */
  public function transmit($response) {
    if (!$this->valid()) {
      $this->sessions->detach($this, $response);
    } else if ($this->modified()) {
      $this->sessions->attach($this, $response);
      $this->close();
    }
  }
}