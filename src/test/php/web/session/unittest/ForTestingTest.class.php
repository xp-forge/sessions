<?php namespace web\session\unittest;

use web\session\ForTesting;
use web\session\ISession;

class ForTestingTest extends SessionsTest {

  #[@test]
  public function can_create() {
    new ForTesting();
  }

  #[@test]
  public function create_session() {
    $factory= new ForTesting();
    $this->assertInstanceOf(ISession::class, $factory->create($this->response()));
  }

  #[@test]
  public function valid() {
    $session= (new ForTesting())->create($this->response());
    $this->assertTrue($session->valid());
  }

  #[@test]
  public function no_longer_valid_after_having_been_destroyed() {
    $session= (new ForTesting())->create($this->response());
    $session->destroy();
    $this->assertFalse($session->valid());
  }

  #[@test]
  public function read_and_write() {
    $session= (new ForTesting())->create($this->response());
    $session->register('test', 'Test the west');
    $this->assertEquals('Test the west', $session->value('test'));
  }

  #[@test]
  public function read_non_existant_returns_default() {
    $session= (new ForTesting())->create($this->response());
    $this->assertEquals('Default value', $session->value('test', 'Default value'));
  }

  #[@test]
  public function remove() {
    $session= (new ForTesting())->create($this->response());
    $session->register('test', 'Test the west');
    $session->remove('test');
    $this->assertNull($session->value('test'));
  }
}