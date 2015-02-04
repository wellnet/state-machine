<?php

namespace Wellnet\StateMachine;

/**
 * Defines the events triggered and consumed by the StateMachine framework.
 *
 * @package Wellnet\StateMachine
 */
class StateMachineEvents {

  // events produced by the StateMachine
  const START = 'state-machine.start';
  const BEFORE_TRANSITION = 'state-machine.before.transition';
  const TRANSITION_SUCCEEDED = 'state-machine.transition.succeeded';
  const TRANSITION_NOT_ALLOWED = 'state-machine.transition.not.allowed';
  const TRANSITION_NOT_AVAILABLE = 'state-machine.transition.not.available';

  // events consumed by the StateMachine
  const INPUT = 'state-machine.input';

}
