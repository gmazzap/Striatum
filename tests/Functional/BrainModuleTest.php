<?php namespace Brain\Striatum\Tests\Functional;

class BrainModuleTest extends \Brain\Striatum\Tests\FunctionalTestCase {

    function testBrain() {
        assertInstanceOf( '\Brain\Container', $this->getBrain() );
    }

    function testModuleBoot() {
        assertInstanceOf( '\ArrayObject', $this->getBrain()->get( 'striatum.hooks' ) );
        assertInstanceOf( '\ArrayObject', $this->getBrain()->get( 'striatum.frozen' ) );
        $ns = 'Brain\\Striatum\\';
        assertInstanceOf( $ns . 'Bucket', $this->getBrain()->get( 'striatum.bucket' ) );
        assertInstanceOf( $ns . 'Subject', $this->getBrain()->get( 'striatum.subject' ) );
        assertInstanceOf( $ns . 'Hook', $this->getBrain()->get( 'striatum.hook' ) );
        assertInstanceOf( $ns . 'SubjectsManager', $this->getBrain()->get( 'striatum.manager' ) );
        //assertInstanceOf( $ns . 'API', $this->getBrain()->get( 'hooks.api' ) );
    }

    function testHooks() {
        assertInstanceOf( 'Brain\Striatum\API', \Brain\Hooks::api() );
    }

}