<?php

namespace Wellnet\StateMachine;

/**
 * GuardInterface that allows all transitions.
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
