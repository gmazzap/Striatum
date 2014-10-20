<?php namespace Brain\Striatum\Tests;

class FunctionalTestCase extends TestCase {

    protected $brain;

    function setUp() {
        parent::setUp();
        \Brain\Container::flush();
        $brain = \Brain\Container::boot( new \Pimple\Container, FALSE, FALSE );
        $module = new \Brain\Striatum\BrainModule;
        $module->getBindings( $brain );
        $module->boot( $brain );
        $this->brain = $brain;
    }

    function getBrain() {
        return $this->brain;
    }

}