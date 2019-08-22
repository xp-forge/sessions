<?php namespace web\session;

interface Transport {

  /**
   * Extract session ID from request
   *
   * @param  web.session.Sessions $sessions
   * @param  web.Request $request
   * @return string
   */
  public function id($sessions, $request);

  /**
   * Attach session to response
   *
   * @param  web.session.Sessions $sessions
   * @param  web.Response $response
   * @param  string $id
   * @return void
   */
  public function attach($sessions, $response, $id);

  /**
   * Detach session from response
   *
   * @param  web.session.Sessions $sessions
   * @param  web.Response $response
   * @return void
   */
  public function detach($sessions, $response);

}