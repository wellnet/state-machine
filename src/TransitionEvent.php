<?php

namespace Wellnet\StateMachine;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class TransitionEvent
 */
class TransitionEvent extends Event {

  /**
   * @var Transition
   */
  private $transition;

  /**
   * @param Transition $transition
   */
  public function __construct(Transition $transition = NULL) {
    $this->transition = $transition;
  }

  /**
   * @return Transition
   */
  public function getTransition() {
    return $this->transition;
  }

}
