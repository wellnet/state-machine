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
   * @var array
   */
  private $context;

  /**
   * @param Transition $transition
   * @param array $context
   */
  public function __construct(Transition $transition = NULL, $context = array()) {
    $this->transition = $transition;
    $this->context = $context;
  }

  /**
   * @return Transition
   */
  public function getTransition() {
    return $this->transition;
  }

  /**
   * @return array
   */
  public function getContext() {
    return $this->context;
  }
}
