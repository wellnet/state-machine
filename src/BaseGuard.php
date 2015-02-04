<?php

namespace Wellnet\StateMachine;

/**
 * Base implementation of the GuardInterface
 */
abstract class BaseGuard implements GuardInterface {

  /**
   * @var Transition
   */
  protected $transition;

  function __construct() {
  }

  /**
   * @param Transition $transition
   */
  public function setTransition(Transition $transition) {
    $this->transition = $transition;
  }
}
