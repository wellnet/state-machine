<?php

namespace Wellnet\StateMachine;

/**
 * Class CallbackGuard
 */
class DummyGuard extends BaseGuard {

  /**
   * @param array $context
   *
   * @return bool
   */
  public function allow($context = array()) {
    return TRUE;
  }
}
