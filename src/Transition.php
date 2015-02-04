<?php

namespace Wellnet\StateMachine;

/**
 * Represents a possible transition between to states of the machine.
 *
 * @package Wellnet\StateMachine
 */
class Transition {

  /**
   * @var State
   */
  private $source;

  /**
   * @var State
   */
  private $destination;

  /**
   * @var string
   */
  private $input;

  /**
   * @var GuardInterface
   */
  private $guard;

  /**
   * @param State $source
   * @param $input
   * @param State $destination
   * @param GuardInterface $guard
   * @internal param $name
   */
  public function __construct(State $source, $input, State $destination, GuardInterface $guard = NULL) {
    $this->source = $source;
    $this->input = $input;
    $this->destination = $destination;

    if (isset($guard)) {
      $guard->setTransition($this);
    }
    $this->guard = $guard;
  }

  /**
   * @return State
   */
  public function getSource() {
    return $this->source;
  }

  /**
   * @return State
   */
  public function getDestination() {
    return $this->destination;
  }

  /**
   * @return string
   */
  public function getInput() {
    return $this->input;
  }

  /**
   * @return GuardInterface
   */
  public function getGuard() {
    return $this->guard;
  }

}
