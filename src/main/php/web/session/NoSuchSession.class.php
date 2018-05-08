<?php namespace web\session;

use lang\XPException;

class NoSuchSession extends XPException {

  /** @param string $id */
  public function __construct($id) {
    parent::__construct('No such session '.$id);
  }
}