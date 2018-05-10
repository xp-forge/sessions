<?php namespace web\session\unittest;

use web\session\ForTesting;
use web\session\ISession;

class ForTestingTest extends SessionsTest {

  /** @return web.session.Sessions */
  protected function fixture() { return new ForTesting(); }

}