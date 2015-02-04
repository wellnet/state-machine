<?php

namespace Wellnet\StateMachine;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event fired by the state machine. It represents a transition.
 *
 * @package Wellnet\StateMachine
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
