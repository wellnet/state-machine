<?php
/**
 * Created by PhpStorm.
 * User: Franz
 * Date: 10/12/2014
 * Time: 06:29
 */

namespace Wellnet\StateMachine;


use Symfony\Component\EventDispatcher\Event;

class InputEvent extends Event {

  /**
   * @var string
   */
  private $input;

  /**
   * @var array
   */
  private $context;

  /**
   * @param null $input
   * @param array $context
   */
  public function __construct($input, array $context = NULL) {
    $this->input = $input;
    $this->context = $context;
  }

  /**
   * @return array
   */
  public function getContext() {
    return $this->context;
  }

  /**
   * @return string
   */
  public function getInput() {
    return $this->input;
  }


}
