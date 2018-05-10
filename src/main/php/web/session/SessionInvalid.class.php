<?php namespace web\session;

use lang\XPException;

class SessionInvalid extends XPException {

  /** @param string $id */
  public function __construct($id) {
    parent::__construct('Session '.$id.' invalid');
  }
}