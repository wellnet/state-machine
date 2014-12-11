<?php
/**
 * Created by PhpStorm.
 * User: Franz
 * Date: 10/12/2014
 * Time: 06:26
 */

namespace Wellnet\StateMachine;


class InputListener {

  private $stateMachine;

  function __construct(StateMachine $stateMachine) {
    $this->stateMachine = $stateMachine;
  }

  public function onStateMachineInput(InputEvent $event) {

    $this->stateMachine->executeTransition(
      $event->getInput(),
      $event->getContext());

  }

}
