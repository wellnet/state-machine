<?php

namespace Wellnet\StateMachine;


use Symfony\Component\EventDispatcher\Event;

/**
 * Event consumed by the state machine. It represents an input sent to the state
 * machine.
 *
 * @package Wellnet\StateMachine
 */
class InputEvent extends Event {

  /**
   * @var string
   */
  private $input;

  /**
   * @var array
   */
  private $context;

  /**
   * @param $input
   * @param array $context
   */
  public function __construct($input, array $context = NULL) {
    $this->input = $input;
    $this->context = $context;
  }

  /**
   * @return array
   */
  public function getContext() {
    return $this->context;
  }

  /**
   * @return string
   */
  public function getInput() {
    return $this->input;
  }

}
