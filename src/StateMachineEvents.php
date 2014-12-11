<?php
/**
 * Created by PhpStorm.
 * User: Franz
 * Date: 01/12/2014
 * Time: 16:35
 */

namespace Wellnet\StateMachine;


class StateMachineEvents {

  // events produced by the StateMachine
  const START = 'state-machine.start';
  const BEFORE_TRANSITION = 'state-machine.before.transition';
  const TRANSITION_SUCCEEDED = 'state-machine.transition.succeeded';
  const TRANSITION_NOT_ALLOWED = 'state-machine.transition.not.allowed';
  const TRANSITION_NOT_AVAILABLE = 'state-machine.transition.not.available';
  //const AFTER_TRANSITION_UNCHANGED = 'state-machine.after.transition.unchanged';

  // events consumed by the StateMachine
  const INPUT = 'state-machine.input';

}
