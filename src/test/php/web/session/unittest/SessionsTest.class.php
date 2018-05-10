<?php namespace web\session\unittest;

use unittest\TestCase;
use web\io\TestInput;
use web\io\TestOutput;
use web\Request;
use web\Response;
use web\session\ISession;
use web\session\NoSuchSession;
use web\session\SessionInvalid;

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

  /** @return web.session.Sessions */
  protected abstract function fixture();

  #[@test]
  public function create() {
    $sessions= $this->fixture();
    $this->assertInstanceOf(ISession::class, $sessions->create());
  }

  #[@test]
  public function open() {
    $sessions= $this->fixture();
    $session= $sessions->create();
    $session->transmit($this->response());

    $session= $sessions->open($this->request('session='.$session->id()));
    $this->assertInstanceOf(ISession::class, $session);
  }

  #[@test, @expect(NoSuchSession::class)]
  public function open_non_existant() {
    $this->fixture()->open($this->request('session=@non-existant@'));
  }

  #[@test]
  public function locate() {
    $sessions= $this->fixture();
    $session= $sessions->create();
    $session->transmit($this->response());

    $session= $sessions->locate($this->request('session='.$session->id()));
    $this->assertInstanceOf(ISession::class, $session);
  }

  #[@test]
  public function locate_non_existant() {
    $this->assertNull($this->fixture()->locate($this->request('session=@non-existant@')));
  }

  #[@test]
  public function valid() {
    $session= $this->fixture()->create();
    $this->assertTrue($session->valid());
  }

  #[@test]
  public function read_write() {
    $session= $this->fixture()->create();
    $session->register('name', 'value');
    $this->assertEquals('value', $session->value('name'));
  }

  #[@test]
  public function read_non_existant() {
    $session= $this->fixture()->create();
    $this->assertNull($session->value('name'));
  }

  #[@test]
  public function read_non_existant_returns_default() {
    $session= $this->fixture()->create();
    $this->assertEquals('Default value', $session->value('name', 'Default value'));
  }

  #[@test]
  public function remove() {
    $session= $this->fixture()->create();
    $session->register('name', 'value');
    $session->remove('name');
    $this->assertNull($session->value('name'));
  }

  #[@test]
  public function read_write_with_two_session_instances() {
    $sessions= $this->fixture();

    $session= $sessions->create();
    $session->register('name', 'value');
    $session->transmit($this->response());

    $session= $sessions->open($this->request('session='.$session->id()));
    $value= $session->value('name');

    $this->assertEquals('value', $value);
  }

  #[@test]
  public function no_longer_valid_after_having_been_destroyed() {
    $session= $this->fixture()->create();
    $session->destroy();
    $this->assertFalse($session->valid());
  }

  #[@test, @expect(SessionInvalid::class)]
  public function cannot_read_after_destroying() {
    $session= $this->fixture()->create();
    $session->destroy();
    $session->value('any');
  }

  #[@test, @expect(SessionInvalid::class)]
  public function cannot_write_after_destroying() {
    $session= $this->fixture()->create();
    $session->destroy();
    $session->register('any', 'value');
  }

  #[@test, @expect(SessionInvalid::class)]
  public function cannot_remove_after_destroying() {
    $session= $this->fixture()->create();
    $session->destroy();
    $session->remove('any');
  }
}