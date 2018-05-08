<?php namespace web\session\unittest;

use unittest\TestCase;
use web\session\InMemory;
use web\session\ISession;

class InMemoryTest extends TestCase {

  #[@test]
  public function can_create() {
    new InMemory();
  }

  #[@test]
  public function create_session() {
    $factory= new InMemory();
    $this->assertInstanceOf(ISession::class, $factory->create());
  }

  #[@test]
  public function valid() {
    $session= (new InMemory())->create();
    $this->assertTrue($session->valid());
  }

  #[@test]
  public function no_longer_valid_after_having_been_destroyed() {
    $session= (new InMemory())->create();
    $session->destroy();
    $this->assertFalse($session->valid());
  }

  #[@test]
  public function read_and_write() {
    $session= (new InMemory())->create();
    $session->register('test', 'Test the west');
    $this->assertEquals('Test the west', $session->value('test'));
  }

  #[@test]
  public function read_non_existant_returns_default() {
    $session= (new InMemory())->create();
    $this->assertEquals('Default value', $session->value('test', 'Default value'));
  }

  #[@test]
  public function remove() {
    $session= (new InMemory())->create();
    $session->register('test', 'Test the west');
    $session->remove('test');
    $this->assertNull($session->value('test'));
  }
}