<?php

namespace Wellnet\StateMachine;

/**
 * Composite of GuardInterface instances.
 *
 * The component guards will be initialized with the same args used for $this.
 *
 * @package Wellnet\StateMachine
 * @see GuardInterface
 */
class CompositeGuard extends BaseGuard {

  private $componentsGuards = array();

  /**
   * @param null $args
   *   associative array whose 'componentGuards' key maps to an array that
   *   contains the full class names of component guards.
   */
  function __construct($args = NULL) {
    if (isset($args['componentGuards'])) {
      foreach ($args['componentGuards'] as $componentGuard) {
        $this->componentsGuards[] = (new \ReflectionClass($componentGuard))->newInstance($args);
      }
    }
  }

  /**
   * @param array $context
   *
   * @return bool
   */
  public function allow($context = array()) {
    $isAllowed = TRUE;
    foreach ($this->componentsGuards as $componentsGuard) {
      $isAllowed = $componentsGuard->allow($context);
      if (!$isAllowed) {
        break;
      }
    }
    return $isAllowed;
  }
}
