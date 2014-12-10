<?php

namespace Wellnet\StateMachine;

/**
 * Class CallbackGuard
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
