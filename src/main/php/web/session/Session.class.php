<?php namespace web\session;

use lang\Closeable;

abstract class Session implements Closeable {
  protected $sessions, $expire;

  /**
   * Creates a new session
   *
   * @param  web.session.Sessions $sessions
   * @param  int $expire
   */
  public function __construct($sessions, $expire) {
    $this->sessions= $sessions;
    $this->expire= $expire;
  }

  /**
   * Returns the session token
   *
   * @return string
   */
  public abstract function token();

  /**
   * Returns whether the session is valid. Returns `true` if current time
   * is past the expire timestamp passed in the constructor in this default
   * implementation.
   *
   * @return bool
   */
  public function valid() { return time() < $this->expire; }

  /**
   * Returns whether the session needs to be attached. Returns `true` in
   * this default implementation.
   *
   * @return bool
   */
  public function attach() { return true; }

  /**
   * Destroys the session. Sets the expire time to one second before the
   * current time in this default implementation.
   *
   * @return void
   */
  public function destroy() { $this->expire= time() - 1; }

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
   * Closes this session and flushes data. Doesn't do a anything in this
   * default implementation.
   *
   * @return void
   */
  public function close() {
    // NOOP
  }

  /**
   * Transmits this session to the response. In case the session token
   * hasn't changed, doesn't attach the session to the response in order
   * to reduce the response header length.
   *
   * @param  web.Response $response
   * @return void
   */
  public final function transmit($response) {
    if (!$this->valid()) {
      $this->sessions->detach($this, $response);
    } else if ($this->attach()) {
      $this->sessions->attach($this, $response);
      $this->close();
    }
  }
}