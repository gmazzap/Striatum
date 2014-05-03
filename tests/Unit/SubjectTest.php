<?php

namespace Brain\Striatum\Tests\Unit;

use Brain\Striatum\Tests\TestCase;
use Brain\Striatum as S;

class SubjectTest extends TestCase {

    protected function get() {
        $bucket = \Mockery::mock( 'Brain\Striatum\Bucket' );
        return new S\Subject( $bucket );
    }

    protected function getMocked( $id = NULL ) {
        $bucket = \Mockery::mock( 'Brain\Striatum\Bucket' );
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' )->makePartial();
        $subject->shouldReceive( 'getHooks' )->withNoArgs()->andReturn( $bucket );
        $subject->setContext();
        if ( ! is_null( $id ) ) {
            $subject->shouldReceive( 'getId' )->withNoArgs()->andReturn( $id );
        }
        return $subject;
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    function testAttachFailsIfNoHookGiven() {
        $subject = $this->get();
        $subject->attach();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testAttachFailsIfBadHookGiven() {
        $observer = \Mockery::mock( 'SplObserver' );
        $subject = $this->get();
        $subject->attach( $observer );
    }

    function testAttach() {
        $hook = \Mockery::mock( 'Brain\Striatum\Hook' );
        $hook->shouldReceive( 'set' )->withAnyArgs()->andReturnNull();
        $subject = $this->getMocked();
        $subject->shouldReceive( 'add' )->once()->with( $hook )->andReturn( $hook );
        $bucket = $subject->getHooks();
        $bucket->shouldReceive( 'add' )->once()->with( $hook )->andReturn( 'Attached!' );
        assertEquals( 'Attached!', $subject->attach( $hook ) );
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    function testDetachFailsIfNoHookGiven() {
        $subject = $this->get();
        $subject->detach();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testDetachFailsIfBadHookGiven() {
        $observer = \Mockery::mock( 'SplObserver' );
        $subject = $this->get();
        $subject->detach( $observer );
    }

    function testDetach() {
        $hook = \Mockery::mock( 'Brain\Striatum\Hook' );
        $subject = $this->getMocked();
        $subject->shouldReceive( 'remove' )->once()->with( $hook )->andReturn( $hook );
        $bucket = $subject->getHooks();
        $bucket->shouldReceive( 'remove' )->once()->with( $hook )->andReturn( 'Detached!' );
        assertEquals( 'Detached!', $subject->detach( $hook ) );
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    function testAddFailsIfBadHookGiven() {
        $subject = $this->get();
        $subject->add( $this );
    }

    function testAdd() {
        $hook = \Mockery::mock( 'Brain\Striatum\Hook' );
        $subject = $this->getMocked( 'save_post' );
        $hook->shouldReceive( 'get' )->once()->with( 'priority' )->andReturn( 10 );
        $hook->shouldReceive( 'get' )->once()->with( 'args_num' )->andReturn( 2 );
        $added = $subject->add( $hook );
        assertActionAdded( 'save_post', [ $hook, 'proxy' ], 10, 2 );
        assertEquals( $hook, $added );
    }

    function testAddWhenIsFilter() {
        $hook = \Mockery::mock( 'Brain\Striatum\Hook' );
        $subject = $this->getMocked( 'the_title' );
        $subject->shouldReceive( 'isFilter' )->once()->withNoArgs()->andReturn( TRUE );
        $hook->shouldReceive( 'get' )->once()->with( 'priority' )->andReturn( 20 );
        $hook->shouldReceive( 'get' )->once()->with( 'args_num' )->andReturn( 3 );
        $added = $subject->add( $hook );
        assertFilterAdded( 'the_title', [ $hook, 'proxy' ], 20, 3 );
        assertEquals( $hook, $added );
    }

}