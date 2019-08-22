<?php namespace web\session;

interface Transport {

  /**
   * Locate session attached to request
   *
   * @param  web.session.Sessions $sessions
   * @param  web.Request $request
   * @return ?web.session.ISession
   */
  public function locate($sessions, $request);

  /**
   * Attach session to response
   *
   * @param  web.session.Sessions $sessions
   * @param  web.Response $response
   * @param  web.session.ISession $session
   * @return void
   */
  public function attach($sessions, $response, $session);

  /**
   * Detach session from response
   *
   * @param  web.session.Sessions $sessions
   * @param  web.Response $response
   * @param  web.session.ISession $session
   * @return void
   */
  public function detach($sessions, $response, $session);

}