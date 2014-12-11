<?php

namespace Wellnet\StateMachine;

use Pimple\Container;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;

/**
 * Entry point of the StateMachine framework.
 *
 * After you create an instance, you may invoke start() to optionally provide
 * an initial state.
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
   * @var boolean
   */
  private $file;


  /**
   * @var boolean
   */
  private $started = FALSE;

  /**
   * @param Container $container
   * @param EventDispatcher $eventDispatcher
   * @param null $file path to a yaml configuration file
   * @internal param State $initial
   */
  public function __construct(Container $container, EventDispatcher $eventDispatcher, $file = NULL) {
    // TODO analysis: accept $config (instead of $file) to remove symfony/yaml dependency and generalize the API
    // TODO improvement: manage set of initial and final states
    $this->container = $container;
    $this->eventDispatcher = $eventDispatcher;
    $this->file = $file;

    $this->eventDispatcher->addListener(
      StateMachineEvents::INPUT,
      array(new InputListener($this), 'onStateMachineInput'));
  }

  private function load() {
    $config = Yaml::parse($this->file);

    $stateClass = isset($this->container['wellnet.state-machine.state']) ?
      $this->container['wellnet.state-machine.state'] : '\\Wellnet\\StateMachine\\State';
    $transitionClass = isset($this->container['wellnet.state-machine.transition']) ?
      $this->container['wellnet.state-machine.transition'] : '\\Wellnet\\StateMachine\\Transition';
    $defaultGuardClass = new $config['defaults']['guard']['class']();

    foreach ($config['transitions'] as $source => $destinations) {
      $from = $this->getOrCreateState($source, $stateClass);
      foreach ($destinations as $destination) {
        $to = $this->getOrCreateState($destination['to'], $stateClass);
        $guard = isset($destination['guard']) ? new $destination['guard']() : $defaultGuardClass;
        $this->addTransition(new $transitionClass($from, $destination['input'], $to, $guard));
      }
    }
    $this->defaultInitialState = $config['defaults']['initialState'];
  }

  /**
   * Starts $this StateMachine.
   *
   * If $initialStateName is not passed, $this instance tries to fall back on
   * the $file['defaults]['initialState'] state passed to the constructor. If
   * neither is available, the method will throw an exception.
   *
   * @param State $initialStateName the name of an existing state
   * @return $this
   * @throws \Exception
   */
  public function start($initialStateName = NULL) {
    $this->checkInitialization();

    if (isset($this->file)) {
      $this->load();
    }

    if (isset($initialStateName)) {
      $this->currentState = $this->getState($initialStateName);
    }
    elseif (isset($this->defaultInitialState)) {
      $this->currentState = $this->getState($this->defaultInitialState);
    }
    else {
      throw new \Exception('An $initialStateName must be provided or a default initial state must be set in the configuration');
    }

    $this->started = TRUE;
    $this->eventDispatcher->dispatch(StateMachineEvents::START, new Event());

    return $this;
  }

  /**
   * @return EventDispatcher
   */
  public function getEventDispatcher() {
    return $this->eventDispatcher;
  }

  /**
   * Returns an array containing the list of accepted input in the current
   * state.
   *
   * WARNING: if the machine has not been started, the array is empty.
   *
   * @return array
   */
  public function getAcceptedInputs() {
    $acceptedInput = array();

    if ($this->started) {
      $currentStateName = $this->currentState->getName();
      if (isset($this->transitions[$currentStateName])) {
        $acceptedInput = $this->transitions[$currentStateName];
      }
    }

    return $acceptedInput;
  }

  /**
   * @param $name
   * @return State
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
   * @param State $state
   * @return $this
   */
  public function addState(State $state) {
    $this->checkInitialization();

    // TODO throw DuplicateStateException or define a behavior
    $this->states[$state->getName()] = $state;
    return $this;
  }

  /**
   * @param Transition $transition
   * @return $this
   */
  public function addTransition(Transition $transition) {
    $this->checkInitialization();

    $source = $transition->getSource()->getName();
    if (!isset($this->transitions[$source])) {
      $this->transitions[$source] = array();
    }
    $this->transitions[$source][$transition->getInput()] = $transition;

    return $this;
  }

  /**
   * @param string $input
   * @param array $context
   *
   * @return $this
   */
  public function executeTransition($input, $context = array()) {
    if (!$this->started) {
      $this->start();
    }

    $transition = $this->getTransition($input);
    $transitionEvent = new TransitionEvent($transition);

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
   * return NULL
   *
   * @return State
   */
  public function getCurrentState() {
    return $this->currentState;
  }

  /**
   * @param name
   * @return State
   */
  private function getState($name) {
    return isset($this->states[$name]) ? $this->states[$name] : NULL;
  }

  /**
   * @param name
   * @return Transition
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
   * If $this StateMachine has already been started, this method throws an
   * Exception.
   *
   * @throws \Exception
   */
  private function checkInitialization() {
    // TODO refactoring: find a better name
    if ($this->started) {
      throw new \Exception('$this StateMachine has already been started');
    }
  }

}
