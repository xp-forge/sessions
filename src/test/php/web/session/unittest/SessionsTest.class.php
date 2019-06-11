<?php namespace web\session\unittest;

use unittest\TestCase;
use web\Request;
use web\Response;
use web\io\TestInput;
use web\io\TestOutput;
use web\session\ISession;
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
  public function named() {
    $sessions= $this->fixture();
    $this->assertEquals('SESS', $sessions->named('SESS')->name());
  }

  #[@test]
  public function in() {
    $sessions= $this->fixture();
    $this->assertEquals('/sub', $sessions->in('/sub')->path());
  }

  #[@test]
  public function secureByDefault() {
    $sessions= $this->fixture();
    $this->assertTrue($sessions->isSecure());
  }

  #[@test]
  public function secureDisabled() {
    $sessions= $this->fixture();
    $this->assertFalse($sessions->disableSecure()->isSecure());
  }

  #[@test]
  public function open() {
    $sessions= $this->fixture();

    $session= $sessions->create();
    $session->register('id', 'Test');
    $session->close();

    $session= $sessions->open($session->id());
    $this->assertEquals('Test', $session->value('id'));
  }

  #[@test]
  public function open_non_existant() {
    $this->assertNull($this->fixture()->open('@non-existant@'));
  }

  #[@test]
  public function locate() {
    $sessions= $this->fixture();

    $session= $sessions->create();
    $session->register('id', 'Test');
    $session->transmit($this->response());

    $session= $sessions->locate($this->request($sessions->name().'='.$session->id()));
    $this->assertEquals('Test', $session->value('id'));
  }

  #[@test]
  public function locate_invalid() {
    $sessions= $this->fixture();

    $session= $sessions->create();
    $session->destroy();
    $session->transmit($this->response());

    $this->assertNull($sessions->locate($this->request($sessions->name().'='.$session->id())));
  }

  #[@test]
  public function locate_non_existant() {
    $sessions= $this->fixture();
    $this->assertNull($sessions->locate($this->request($sessions->name().'=@non-existant@')));
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
    $this->assertTrue($session->remove('name'));
    $this->assertNull($session->value('name'));
  }

  #[@test]
  public function remove_non_existant() {
    $session= $this->fixture()->create();
    $this->assertFalse($session->remove('name'));
    $this->assertNull($session->value('name'));
  }

  #[@test]
  public function read_write_with_two_session_instances() {
    $sessions= $this->fixture();

    $session= $sessions->create();
    $session->register('name', 'value');
    $session->transmit($this->response());

    $session= $sessions->open($session->id());
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

  #[@test]
  public function keys_initially_empty() {
    $session= $this->fixture()->create();
    $this->assertEquals([], $session->keys());
  }

  #[@test]
  public function key() {
    $session= $this->fixture()->create();
    $session->register('name', 'value');
    $this->assertEquals(['name'], $session->keys());
  }

  #[@test]
  public function keys() {
    $session= $this->fixture()->create();
    $session->register('name1', 'value1');
    $session->register('name2', 'value2');
    $this->assertEquals(['name1', 'name2'], $session->keys());
  }
}