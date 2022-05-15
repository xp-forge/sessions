<?php namespace web\session\unittest;

use util\Secret;
use web\session\CookieBased;

class CookieBasedTest extends SessionsTest {

  /** @return web.session.Sessions */
  protected function fixture() { return new CookieBased(new Secret('tlw3/ELaLfu3kmpzQJ0MDCdRG2b8Le+X')); }

}