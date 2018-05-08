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
   * Closes the session
   *
   * @return void
   */
  public function close();

  /**
   * Destroys the session
   *
   * @return void
   */
  public function destroy();

  /**
   * Registers a value - writing it to the session
   *
   * @param  string $name
   * @param  var $value
   * @return void
   */
  public function register($name, $value);

  /**
   * Retrieves a value - reading it from the session
   *
   * @param  string $name
   * @param  var $default
   * @return var
   */
  public function value($name, $default= null);

  /**
   * Removes a value - deleting it from the session
   *
   * @param  string $name
   * @return void
   */
  public function remove($name);
}