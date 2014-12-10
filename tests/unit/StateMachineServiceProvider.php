<?php

namespace Wellnet\StateMachine;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class RwfServiceProvider
 */
class StateMachineServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(Container $pimple) {
    $pimple['rwf.event_dispatcher'] = function ($c) {
      return new EventDispatcher();
    };
    $pimple['rwf.state_machine_config'] = function ($c) {
      $file = file_get_contents(__DIR__ . '\..\fixtures\test.yml');
      return $file;
    };
    $pimple['rwf.state_machine'] = $pimple->factory(function ($c) {
      return new StateMachine($c['rwf.event_dispatcher'], $c['rwf.state_machine_config']);
    });
  }
}
