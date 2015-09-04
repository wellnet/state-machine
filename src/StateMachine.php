<?php

namespace Wellnet\StateMachine;

use Pimple\Container;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Entry point of the StateMachine framework.
 *
 * You can configure the state machine leveraging the API (addState and
 * addTransition methods) or providing a configuration object to the constructor.
 * See __construct() for more details.
 *
 * After you create an instance, you may invoke start() to optionally provide
 * an initial state.
 *
 * You can invoke the executeTransition() method to send an input to the state
 * machine.
 *
 */
class StateMachine {

  /**
   * @var State[]
   */
  private $states = array();

  /**
   * @var Transition[]
   */
  private $transitions = array();

  /**
   * @var State
   */
  private $currentState = NULL;

  // TODO refactor: there's risk of using it as a service locator
  /**
   * @var Container
   */
  private $container;

  /**
   * @var EventDispatcher
   */
  private $eventDispatcher;

  /**
   * @var string
   */
  private $defaultInitialState;

  /**
   * @var ConfiguratorInterface
   */
  private $configurator;

  /**
   * @var boolean
   */
  private $initialized = FALSE;

  /**
   * Creates a new instance.
   *
   * Inside the $container, you can provide the name of a State extension
   * (with key 'wellnet.state-machine.state') and/or the name of a Transition
   * extension (with key 'wellnet.state-machine.transition') to customize the
   * behavior.
   *
   * Registers an InputListener with the StateMachineEvents::INPUT event.
   *
   * @param Container $container
   * @param EventDispatcher $eventDispatcher
   * @param ConfiguratorInterface $configurator
   */
  public function __construct(Container $container, EventDispatcher $eventDispatcher, ConfiguratorInterface $configurator) {
    // TODO improvement: manage set of initial and final states
    $this->container = $container;
    $this->eventDispatcher = $eventDispatcher;
    $this->configurator = $configurator;

    $this->eventDispatcher->addListener(
      StateMachineEvents::INPUT,
      array(new InputListener($this), 'onStateMachineInput'));
  }

  /**
   * builds the state machine from the configuration object
   */
  private function load() {
    $config = $this->configurator->getConfig();

    // check if the container provides custom implementations of State or Transition
    $stateClass = isset($this->container['wellnet.state-machine.state']) ?
      $this->container['wellnet.state-machine.state'] : '\\Wellnet\\StateMachine\\State';
    $transitionClass = isset($this->container['wellnet.state-machine.transition']) ?
      $this->container['wellnet.state-machine.transition'] : '\\Wellnet\\StateMachine\\Transition';
    $defaultGuardClass = $config['defaults']['guard']['class'];

    // loops on transition sources; the states are created automatically when a new
    // source or a new destination is encountered.
    foreach ($config['transitions'] as $source => $destinations) {
      $from = $this->getOrCreateState($source, $stateClass);
      // loops on transition destinations (for a given source)
      foreach ($destinations as $destination) {
        $to = $this->getOrCreateState($destination['to'], $stateClass);
        // creates the Guard instance for a transition; if the Guard class is
        // not specified for this transition, use the default one
        $guardClass = isset($destination['guard']) ?
          $destination['guard'] : $defaultGuardClass;
        $guardArgs = isset($destination['guardArgs']) ?
          $destination['guardArgs'] : array();
        $guard = (new \ReflectionClass($guardClass))->newInstance($guardArgs);
        $this->addTransition(new $transitionClass($from, $destination['input'], $to, $guard));
      }
    }
    $this->defaultInitialState = $config['defaults']['initialState'];
  }

  /**
   * initializes the state machine
   *
   * @param null $stateName
   * @throws \Exception
   */
  private function init($stateName = NULL) {
    $this->preventReinitialization();
    $this->load();

    if (isset($stateName)) {
      $this->currentState = $this->getState($stateName);
    }
    elseif (isset($this->defaultInitialState)) {
      $this->currentState = $this->getState($this->defaultInitialState);
    }
    else {
      throw new \Exception('An $initialStateName must be provided or a default initial state must be set in the configuration');
    }

    $this->initialized = TRUE;
  }

  /**
   * Starts $this StateMachine.
   *
   * If $initialStateName is not passed, $this instance tries to fall back on
   * the $file['defaults]['initialState'] state passed to the constructor. If
   * neither is available, the method will throw an exception.
   *
   * This method fires the StateMachineEvents::START events:
   *
   * @param $initialStateName
   * @return $this
   * @throws \Exception
   */
  public function start($initialStateName = NULL) {
    $this->init($initialStateName);
    $this->eventDispatcher->dispatch(StateMachineEvents::START, new Event());
    return $this;
  }

  /**
   * Returns TRUE if $this instance has already been initialized.
   *
   * @return bool
   */
  public function isRunning() {
    return $this->initialized;
  }

  /**
   * Resume $this instance from a given state. Unlike the start() method, this
   * one does not fire events.
   *
   * @param $resumedState
   * @return $this
   * @throws \Exception
   */
  public function resume($resumedState) {
    $this->init($resumedState);
    return $this;
  }

  /**
   * @return EventDispatcher
   */
  public function getEventDispatcher() {
    return $this->eventDispatcher;
  }

  /**
   * Returns an array containing the list of accepted inputs in the current
   * state.
   *
   * WARNING: if the machine has not been started, the array is empty.
   *
   * @param array $context
   * @return array
   */
  public function getAllowedTransitions($context = array()) {
    $allowedTransitions = array();

    if ($this->initialized) {
      $currentStateName = $this->currentState->getName();
      if (isset($this->transitions[$currentStateName])) {
        $availableTransitions = $this->transitions[$currentStateName];
        foreach ($availableTransitions as $transition) {
          // invoke the guard to determine if a transition is allowed
          if ($transition->getGuard()->allow($context)){
            $allowedTransitions[] = $transition;
          }
        }
      }
    }

    return $allowedTransitions;
  }

  /**
   * Returns the state identified by the given name. If it does not exist yet,
   * it is created and added to $this state machine.
   *
   * @param $name
   *   the name of the state to return
   * @param $stateClass
   *   the name of the class to instantiate
   * @return State
   *
   * @see State
   */
  private function getOrCreateState($name, $stateClass) {
    $state = $this->getState($name);
    if ($state == NULL) {
      $state = new $stateClass($name);
      $this->addState($state);
    }
    return $state;
  }

  /**
   * Adds a state to $this state machine.
   *
   * @param State $state
   * @return $this
   *
   * @throws \Exception
   *   if the state already exists in $this state machine
   */
  public function addState(State $state) {
    $this->preventReinitialization();

    if (isset($this->states[$state->getName()])) {
      throw new \Exception('There is already a state named ' . '$state');
    }

    $this->states[$state->getName()] = $state;
    return $this;
  }

  /**
   * Adds a transition to $this state machine.
   *
   * @param Transition $transition
   * @return $this
   */
  public function addTransition(Transition $transition) {
    $this->preventReinitialization();

    $source = $transition->getSource()->getName();
    if (!isset($this->transitions[$source])) {
      $this->transitions[$source] = array();
    }
    $this->transitions[$source][$transition->getInput()] = $transition;

    return $this;
  }

  /**
   * Sends an input to the state machine.
   *
   * This method fires the following events:
   * - StateMachineEvents::BEFORE_TRANSITION
   * - StateMachineEvents::TRANSITION_NOT_AVAILABLE
   * - StateMachineEvents::TRANSITION_NOT_ALLOWED
   * - StateMachineEvents::TRANSITION_SUCCEEDED
   *
   * @param string $input
   * @param array $context
   *   the $context to pass to the Guard of the possible transition
   *
   * @return $this
   */
  public function executeTransition($input, $context = array()) {
    if (!$this->initialized) {
      $this->start();
    }

    $transition = $this->getTransition($input);
    $transitionEvent = new TransitionEvent($transition, $context);

    $this->eventDispatcher->dispatch(StateMachineEvents::BEFORE_TRANSITION, $transitionEvent);

    if ($transition === NULL) {
      $this->eventDispatcher->dispatch(StateMachineEvents::TRANSITION_NOT_AVAILABLE, $transitionEvent);
    }
    elseif (!$transition->getGuard()->allow($context)) {
      $this->eventDispatcher->dispatch(StateMachineEvents::TRANSITION_NOT_ALLOWED, $transitionEvent);
    }
    else {
      $this->currentState = $transition->getDestination();
      $this->eventDispatcher->dispatch(StateMachineEvents::TRANSITION_SUCCEEDED, $transitionEvent);
    }

    return $this;
  }

  /**
   * Returns the current State if the machine has been started, otherwise it
   * return NULL.
   *
   * @return State
   */
  public function getCurrentState() {
    return $this->currentState;
  }

  /**
   * Returns a state identified by the given $name or NULL.
   *
   * @param name
   * @return State
   */
  private function getState($name) {
    return isset($this->states[$name]) ? $this->states[$name] : NULL;
  }

  /**
   * Returns a transition that has the current state as source and the given
   * parameter as input.
   *
   * @param name
   * @return Transition
   *   a Transition instance or NULL
   */
  private function getTransition($input) {

    $source = $this->currentState->getName();
    if (!isset($this->transitions[$source])) {
      // no more transitions from this state: it is a final state
      return NULL;
    }

    $transitionsBySource = $this->transitions[$source];
    if (!isset($transitionsBySource[$input])) {
      return NULL;
    }

    return $transitionsBySource[$input];
  }

  /**
   * Throws an Exception if $this StateMachine has already been started.
   *
   * @throws \Exception
   */
  private function preventReinitialization() {
    if ($this->initialized) {
      throw new \Exception('$this StateMachine has already been started');
    }
  }

}
