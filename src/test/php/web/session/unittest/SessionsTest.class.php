<?php namespace web\session\unittest;

use unittest\{Expect, Test, TestCase};
use web\io\{TestInput, TestOutput};
use web\session\{Cookies, Session, SessionInvalid};
use web\{Request, Response};

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

  #[Test]
  public function create() {
    $sessions= $this->fixture();
    $this->assertInstanceOf(Session::class, $sessions->create());
  }

  #[Test]
  public function named() {
    $sessions= $this->fixture();
    $this->assertEquals('SESS', $sessions->named('SESS')->name());
  }

  #[Test]
  public function lasting() {
    $sessions= $this->fixture();
    $this->assertEquals(43200, $sessions->lasting(43200)->duration());
  }

  #[Test]
  public function via() {
    $cookies= (new Cookies())->path('/sub');
    $sessions= $this->fixture();
    $this->assertEquals($cookies, $sessions->via($cookies)->cookies());
  }

  #[Test]
  public function open() {
    $sessions= $this->fixture();

    $session= $sessions->create();
    $session->register('id', 'Test');
    $session->close();

    $session= $sessions->open($session->token());
    $this->assertEquals('Test', $session->value('id'));
  }

  #[Test]
  public function open_non_existant() {
    $this->assertNull($this->fixture()->open('@non-existant@'));
  }

  #[Test]
  public function locate() {
    $sessions= $this->fixture();

    $session= $sessions->create();
    $session->register('id', 'Test');
    $session->transmit($this->response());

    $session= $sessions->locate($this->request($sessions->name().'='.$session->token()));
    $this->assertEquals('Test', $session->value('id'));
  }

  #[Test]
  public function locate_invalid() {
    $sessions= $this->fixture();

    $session= $sessions->create();
    $session->destroy();
    $session->transmit($this->response());

    $this->assertNull($sessions->locate($this->request($sessions->name().'='.$session->token())));
  }

  #[Test]
  public function locate_non_existant() {
    $sessions= $this->fixture();
    $this->assertNull($sessions->locate($this->request($sessions->name().'=@non-existant@')));
  }

  #[Test]
  public function attach() {
    $sessions= $this->fixture();
    $response= $this->response();

    $session= $sessions->create();
    $session->register('id', 'Test');
    $session->transmit($response);

    $cookie= $response->cookies()[0];
    $this->assertEquals([$sessions->name() => $session->token()], [$cookie->name() => $cookie->value()]);
  }

  #[Test]
  public function detach() {
    $sessions= $this->fixture();
    $response= $this->response();

    $session= $sessions->create();
    $session->destroy();
    $session->transmit($response);

    $cookie= $response->cookies()[0];
    $this->assertEquals([$sessions->name() => ''], [$cookie->name() => $cookie->value()]);
  }

  #[Test]
  public function detach_uses_path() {
    $sessions= $this->fixture()->via((new Cookies())->path('/testing'));
    $response= $this->response();

    $session= $sessions->create();
    $session->destroy();
    $session->transmit($response);

    $cookie= $response->cookies()[0];
    $this->assertEquals('/testing', $cookie->attributes()['path']);
  }

  #[Test]
  public function detach_uses_domain() {
    $sessions= $this->fixture()->via((new Cookies())->domain('example.org'));
    $response= $this->response();

    $session= $sessions->create();
    $session->destroy();
    $session->transmit($response);

    $cookie= $response->cookies()[0];
    $this->assertEquals('example.org', $cookie->attributes()['domain']);
  }

  #[Test]
  public function valid() {
    $session= $this->fixture()->create();
    $this->assertTrue($session->valid());
  }

  #[Test]
  public function read_write() {
    $session= $this->fixture()->create();
    $session->register('name', 'value');
    $this->assertEquals('value', $session->value('name'));
  }

  #[Test]
  public function read_non_existant() {
    $session= $this->fixture()->create();
    $this->assertNull($session->value('name'));
  }

  #[Test]
  public function read_non_existant_returns_default() {
    $session= $this->fixture()->create();
    $this->assertEquals('Default value', $session->value('name', 'Default value'));
  }

  #[Test]
  public function remove() {
    $session= $this->fixture()->create();
    $session->register('name', 'value');
    $this->assertTrue($session->remove('name'));
    $this->assertNull($session->value('name'));
  }

  #[Test]
  public function remove_non_existant() {
    $session= $this->fixture()->create();
    $this->assertFalse($session->remove('name'));
    $this->assertNull($session->value('name'));
  }

  #[Test]
  public function read_write_with_two_session_instances() {
    $sessions= $this->fixture();

    $session= $sessions->create();
    $session->register('name', 'value');
    $session->transmit($this->response());

    $session= $sessions->open($session->token());
    $value= $session->value('name');

    $this->assertEquals('value', $value);
  }

  #[Test]
  public function no_longer_valid_after_having_been_destroyed() {
    $session= $this->fixture()->create();
    $session->destroy();
    $this->assertFalse($session->valid());
  }

  #[Test, Expect(SessionInvalid::class)]
  public function cannot_read_after_destroying() {
    $session= $this->fixture()->create();
    $session->destroy();
    $session->value('any');
  }

  #[Test, Expect(SessionInvalid::class)]
  public function cannot_write_after_destroying() {
    $session= $this->fixture()->create();
    $session->destroy();
    $session->register('any', 'value');
  }

  #[Test, Expect(SessionInvalid::class)]
  public function cannot_remove_after_destroying() {
    $session= $this->fixture()->create();
    $session->destroy();
    $session->remove('any');
  }

  #[Test]
  public function keys_initially_empty() {
    $session= $this->fixture()->create();
    $this->assertEquals([], $session->keys());
  }

  #[Test]
  public function key() {
    $session= $this->fixture()->create();
    $session->register('name', 'value');
    $this->assertEquals(['name'], $session->keys());
  }

  #[Test]
  public function keys() {
    $session= $this->fixture()->create();
    $session->register('name1', 'value1');
    $session->register('name2', 'value2');
    $this->assertEquals(['name1', 'name2'], $session->keys());
  }
}