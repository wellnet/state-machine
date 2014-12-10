<?php

use Wellnet\StateMachine\StateMachine;
use Wellnet\StateMachine\State;
use Wellnet\StateMachine\Transition;
use Pimple\Container;

/**
 * Created by PhpStorm.
 * User: Franz
 * Date: 28/11/2014
 * Time: 11:17
 */
class StateMachineTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var Container[]
   */
  private static $c;

  public static function setUpBeforeClass() {
    self::$c = new Container();
    self::$c->register(new Wellnet\StateMachine\StateMachineServiceProvider());
  }

  public function testUnstartedMachine() {
    /** @var StateMachine $machine */
    $machine = self::$c['rwf.state_machine'];
    $this->assertNull($machine->getCurrentState());
  }

  /**
   * @expectedException Exception
   */
  public function testStartTwice() {
    /** @var StateMachine $machine */
    $machine = self::$c['rwf.state_machine']
      ->executeTransition('review', array('user' => NULL))
      ->start();
  }

  /**
   * @expectedException Exception
   */
  public function testAddStateAfterStart() {
    /** @var StateMachine $machine */
    $machine = self::$c['rwf.state_machine']
      ->executeTransition('review', array('user' => NULL))
      ->addState(new State('new'));
  }

  /**
   * @expectedException Exception
   */
  public function testAddTransitionAfterStart() {
    /** @var StateMachine $machine */
    $t = new Transition(new State('new'), 'input', new State('new2'));
    $machine = self::$c['rwf.state_machine']
      ->executeTransition('review', array('user' => NULL))
      ->addTransition($t);
  }

  public function workflowProvider() {
    return array(
        array(
          array('draft', 'review', 'reject', 'schedule', 'publish', 'archive', 'unpublish'),
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
    $machine = self::$c['rwf.state_machine'];
    foreach($inputs as $input) {
      $machine ->executeTransition($input, array('user' => NULL));
    }
    $this->assertequals($machine->getCurrentState()->getName(), $finalState);
  }

  public function testExecuteInvalidTransition() {
    /** @var StateMachine $machine */
    $machine = self::$c['rwf.state_machine']
      ->executeTransition('reject', array('user' => NULL));
  }

  public function testAcceptedInputs() {
    /** @var StateMachine $machine */
    $machine = self::$c['rwf.state_machine'];
    $machine->start();
    $this->assertCount(3, $machine->getAcceptedInputs());
  }

}
