<?php namespace web\session\unittest;

use unittest\TestCase;
use web\Request;
use web\Response;
use web\io\TestInput;
use web\io\TestOutput;

abstract class SessionsTest extends TestCase {

  /**
   * Creates a request
   *
   * @param  string $cookie
   * @return web.Request
   */
  protected function request($cookie= null) {
    return new Request(new TestInput('GET', '/', $cookie ? ['Cookie' => $cookie] : []));
  }

  /**
   * Creates a response
   *
   * @return web.Response
   */
  protected function response() {
    return new Response(new TestOutput());
  }
}