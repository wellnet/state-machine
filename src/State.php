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
   * @param State $otherState
   *
   * @return bool
   */
  public function equal(State $otherState) {
    return $this->getName() === $otherState->getName();
  }

}
