<?php

use Wellnet\StateMachine\StateMachine;
use Wellnet\StateMachine\State;
use Wellnet\StateMachine\Transition;
use Wellnet\StateMachine\StateMachineEvents;
use Wellnet\StateMachine\TestEventSubscriber;
use Pimple\Container;

class StateMachineTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var Container[]
   */
  private static $c;

  public static function setUpBeforeClass() {
    self::$c = new Container();
    self::$c->register(new Wellnet\StateMachine\TestServiceProvider());
  }

  public function testMachineNotStarted() {
    /** @var StateMachine $machine */
    $machine = self::$c['state_machine'];
    $this->assertNull($machine->getCurrentState());
    $this->assertEmpty($machine->getAllowedTransitions());
  }

  /**
   * @expectedException Exception
   */
  public function testStartTwice() {
    /** @var StateMachine $machine */
    $machine = self::$c['state_machine']
      ->executeTransition('review', array('user' => NULL))
      ->start();
  }

  /**
   * @expectedException Exception
   */
  public function testAddStateAfterStart() {
    /** @var StateMachine $machine */
    $machine = self::$c['state_machine']
      ->executeTransition('review', array('user' => NULL))
      ->addState(new State('new'));
  }

  /**
   * @expectedException Exception
   */
  public function testAddTransitionAfterStart() {
    /** @var StateMachine $machine */
    $t = new Transition(new State('new'), 'input', new State('new2'));
    $machine = self::$c['state_machine']
      ->executeTransition('review', array('user' => NULL))
      ->addTransition($t);
  }

  public function workflowProvider() {
    return array(
      array(
        array(
          'draft',
          'review',
          'reject',
          'schedule',
          'publish',
          'archive',
          'unpublish'
        ),
        'unpublished'
      ),
      array(
        array('schedule', 'publish'),
        'published'
      )
    );
  }

  /**
   * @dataProvider workflowProvider
   */
  public function testValidWorkflow($inputs, $finalState) {
    /** @var StateMachine $machine */
    $machine = self::$c['state_machine'];
    foreach ($inputs as $input) {
      $machine->executeTransition($input, array('user' => NULL));
    }
    $this->assertEquals($machine->getCurrentState()->getName(), $finalState);
  }

  public function testNotAvailableTransition() {
    /** @var StateMachine $machine */
    $machine = self::$c['state_machine'];
    $subscriber = new TestEventSubscriber();
    $machine->getEventDispatcher()->addSubscriber($subscriber);
    $machine->executeTransition('reject', array('user' => NULL));
    $this->assertContains(StateMachineEvents::TRANSITION_NOT_AVAILABLE, $subscriber->getEvents());
  }

  public function testAcceptedInputs() {
    /** @var StateMachine $machine */
    $machine = self::$c['state_machine'];
    $machine->start();
    $this->assertCount(3, $machine->getAllowedTransitions());
  }

}
