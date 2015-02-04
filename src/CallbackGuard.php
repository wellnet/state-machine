<?php

namespace Wellnet\StateMachine;

/**
 * GuardInterface implementation that leverages a callback (provided by the
 * constructor) that accept the $context as parameter.
 */
class CallbackGuard extends BaseGuard {

  /**
   * @var Callable
   */
  private $callback;

  /**
   * @param Callable $callback
   */
  public function __construct($callback) {
    $this->callback = $callback;
  }

  /**
   * @param array $context
   *
   * @return bool
   */
  public function allow($context = array()) {
    return call_user_func_array($this->callback, array($this->transition, $context));
  }
}
