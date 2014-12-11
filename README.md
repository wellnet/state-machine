state-machine
=============

A simple event-driven state machine

Resources
---------

To run the unit tests, you have to map the Wellnet\StateMachine\ namespace to the state-machine/tests/unit folder.
Simply open composer.json and replace the existing PSR-4 autoload mapping with the following one:

    $ "psr-4": {
    $     "Wellnet\\StateMachine\\": ["src/", "tests/unit/"]
    $ }

Then you can use the following commands:

    $ cd path/to/wellnet/state-machine/
    $ composer.phar install
    $ phpunit