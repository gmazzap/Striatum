<?php namespace Brain\Striatum\Tests;

class FunctionalTestCase extends TestCase {

    protected $brain;

    function setUp() {
        parent::setUp();
        $brain = \Brain\Container::boot( new \Pimple, FALSE, FALSE );
        $module = new \Brain\Striatum\BrainModule;
        $module->getBindings( $brain );
        $module->boot( $brain );
        $this->brain = $brain;
    }

    function tearDown() {
        \Brain\Container::flush();
        parent::tearDown();
    }

    function getBrain() {
        return $this->brain;
    }

}