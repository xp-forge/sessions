<?php namespace web\session;

interface ISession {

  /**
   * Returns the session identifier
   *
   * @return string
   */
  public function id();

  /**
   * Returns whether the session is valid
   *
   * @return bool
   */
  public function valid();

  /**
   * Destroys the session
   *
   * @return void
   */
  public function destroy();

  /**
   * Returns all session keys
   *
   * @return string[]
   */
  public function keys();

  /**
   * Registers a value - writing it to the session
   *
   * @param  string $name
   * @param  var $value
   * @return void
   * @throws web.session.SessionInvalid
   */
  public function register($name, $value);

  /**
   * Retrieves a value - reading it from the session
   *
   * @param  string $name
   * @param  var $default
   * @return var
   * @throws web.session.SessionInvalid
   */
  public function value($name, $default= null);

  /**
   * Removes a value - deleting it from the session
   *
   * @param  string $name
   * @return void
   * @throws web.session.SessionInvalid
   */
  public function remove($name);

  /**
   * Closes this session
   *
   * @return void
   */
  public function close();

  /**
   * Closes and transmits this session to the response
   *
   * @param  web.Response $response
   * @return void
   */
  public function transmit($response);
}