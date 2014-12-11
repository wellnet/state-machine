<?php

namespace Wellnet\StateMachine;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class RwfServiceProvider
 */
class TestServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(Container $pimple) {
    $pimple['event_dispatcher'] = function ($c) {
      return new EventDispatcher();
    };
    $pimple['state_machine_config'] = function ($c) {
      $file = file_get_contents(__DIR__ . '/../fixtures/test.yml');
      return $file;
    };
    $pimple['state_machine'] = $pimple->factory(function ($c) {
      return new StateMachine($c, $c['event_dispatcher'], $c['state_machine_config']);
    });
  }
}
