<?php namespace web\session;

/** Base class for session stores */
abstract class Persistence implements ISession {
  protected $sessions, $detached, $expires;

  /**
   * Creates a new in-memory session
   *
   * @param  web.session.Sessions $sessions
   * @param  bool $detached
   * @param  int $expires
   */
  public function __construct($sessions, $detached, $expires) {
    $this->sessions= $sessions;
    $this->detached= $detached;
    $this->expires= $expires;
  }

  /** @return bool */
  public function valid() { return time() < $this->expires; }

  /** @return int */
  public function expires() { return $this->expires; }

  /**
   * Transmits this session to the response
   *
   * @param  web.Response $response
   * @return void
   */
  public function transmit($response) {
    if ($this->detached) {
      $this->sessions->attach($this, $response);
      $this->detached= false;
      // Fall through, writing session data
    } else if (time() >= $this->expires) {
      $this->sessions->detach($this, $response);
      return;
    }

    $this->close();
  }
}