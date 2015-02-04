<?php

namespace Wellnet\StateMachine;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestEventSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    $handler = array('onEvent', 0);
    return array(
      StateMachineEvents::INPUT => $handler,
      StateMachineEvents::START => $handler,
      StateMachineEvents::BEFORE_TRANSITION => $handler,
      StateMachineEvents::TRANSITION_SUCCEEDED => $handler,
      StateMachineEvents::TRANSITION_NOT_ALLOWED => $handler,
      StateMachineEvents::TRANSITION_NOT_AVAILABLE => $handler,
    );
  }

  private $events = array();

  /**
   * @return array
   */
  public function getEvents() {
    return $this->events;
  }

  public function onEvent(Event $event) {
    $this->events[] = $event->getName();
  }

} 