<?php

namespace Wellnet\StateMachine;


/**
 * listener designed to be registered with the StateMachineEvents::INPUT event
 * @package Wellnet\StateMachine
 */
class InputListener {

  private $stateMachine;

  function __construct(StateMachine $stateMachine) {
    $this->stateMachine = $stateMachine;
  }

  /**
   * Handler invoked to manage the event.
   *
   * @param InputEvent $event
   */
  public function onStateMachineInput(InputEvent $event) {

    $this->stateMachine->executeTransition(
      $event->getInput(),
      $event->getContext());

  }

}
