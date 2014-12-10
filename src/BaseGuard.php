<?php

namespace Wellnet\StateMachine;

/**
 * Class BaseGuard
 */
abstract class BaseGuard implements GuardInterface {

  /**
   * @var
   */
  protected $transition;


  /**
   * @param Transition $transition
   */
  public function setTransition(Transition $transition) {
    $this->transition = $transition;
  }
}
