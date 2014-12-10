<?php

namespace Wellnet\StateMachine;

/**
 * Class State
 */
class State {

  /**
   * @var string
   */
  private $name;

  /**
   * @var StateMachine
   */
  private $stateMachine;

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
   * @param StateMachine $stateMachine
   */
  public function setStateMachine(StateMachine $stateMachine) {
    $this->stateMachine = $stateMachine;
  }

  /**
   * @param State $otherState
   *
   * @return bool
   */
  public function equal(State $otherState) {
    return $this->getName() === $otherState->getName();
  }

} 