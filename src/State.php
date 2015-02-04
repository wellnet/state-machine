<?php

namespace Wellnet\StateMachine;

/**
 * Represents a state of the machine.
 *
 * @package Wellnet\StateMachine
 */
class State {

  /**
   * @var string
   */
  private $name;

  /**
   * @param $name
   */
  public function __construct($name) {
    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Two states are equal if they have the same name.
   *
   * @param State $otherState
   * @return bool
   */
  public function equal(State $otherState) {
    return $this->getName() === $otherState->getName();
  }

}
