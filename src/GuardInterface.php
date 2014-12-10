<?php

namespace Wellnet\StateMachine;

/**
 * Interface GuardInterface
 */
interface GuardInterface {

  /**
   * @param array $context
   *
   * @return bool
   */
  public function allow($context = array());

  /**
   * @param Transition $transition
   */
  public function setTransition(Transition $transition);
}
