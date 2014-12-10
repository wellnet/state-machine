<?php
/**
 * Created by PhpStorm.
 * User: Franz
 * Date: 01/12/2014
 * Time: 16:35
 */

namespace Wellnet\StateMachine;


class RwfEvents {

  // events produced by the StateMachine
  const BEFORE_TRANSITION = 'rwf.before.transition';
  const AFTER_TRANSITION_SUCCEEDED = 'rwf.after.transition.succeeded';
  const AFTER_TRANSITION_FAILED = 'rwf.after.transition.failed';
  //const AFTER_TRANSITION_UNCHANGED = 'rwf.after.transition.unchanged';

  // events consumed by the StateMachine
  const INPUT = 'rwf.input';

}
