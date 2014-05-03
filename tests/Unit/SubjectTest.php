<?php

namespace Brain\Striatum\Tests\Unit;

use Brain\Striatum\Tests\TestCase;
use Brain\Striatum\Tests\HooksMock as HM;
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
        $hook->shouldReceive( 'set' )->atLeast( 1 )->withAnyArgs()->andReturnNull();
        $subject = $this->getMocked();
        $subject->shouldReceive( 'add' )->atLeast( 1 )->with( $hook )->andReturn( $hook );
        $bucket = $subject->getHooks();
        $bucket->shouldReceive( 'add' )->atLeast( 1 )->with( $hook )->andReturn( 'Attached!' );
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
        $subject->shouldReceive( 'remove' )->atLeast( 1 )->with( $hook )->andReturn( $hook );
        $bucket = $subject->getHooks();
        $bucket->shouldReceive( 'remove' )->atLeast( 1 )->with( $hook )->andReturn( 'Detached!' );
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
        $hook->shouldReceive( 'get' )->atLeast( 1 )->with( 'priority' )->andReturn( 10 );
        $hook->shouldReceive( 'get' )->atLeast( 1 )->with( 'args_num' )->andReturn( 2 );
        $added = $subject->add( $hook );
        assertTrue( HM::hasAction( 'save_post', [ $hook, 'proxy' ], 10 ) );
        assertActionAdded( 'save_post', [ $hook, 'proxy' ], 10, 2 );
        assertEquals( $hook, $added );
    }

    function testAddWhenIsFilter() {
        $hook = \Mockery::mock( 'Brain\Striatum\Hook' );
        $subject = $this->getMocked( 'the_title' );
        $subject->shouldReceive( 'isFilter' )->atLeast( 1 )->withNoArgs()->andReturn( TRUE );
        $hook->shouldReceive( 'get' )->atLeast( 1 )->with( 'priority' )->andReturn( 20 );
        $hook->shouldReceive( 'get' )->atLeast( 1 )->with( 'args_num' )->andReturn( 3 );
        $added = $subject->add( $hook );
        assertTrue( HM::hasFilter( 'the_title', [$hook, 'proxy' ], 20 ) );
        assertFilterAdded( 'the_title', [ $hook, 'proxy' ], 20, 3 );
        assertEquals( $hook, $added );
    }

    /**
     * @expectedException Brain\Striatum\Tests\HookException
     */
    function testRemove() {
        $hook = \Mockery::mock( 'Brain\Striatum\Hook' );
        $subject = $this->getMocked( 'save_post' );
        $hook->shouldReceive( 'get' )->atLeast( 1 )->with( 'priority' )->andReturn( 10 );
        $hook->shouldReceive( 'get' )->atLeast( 1 )->with( 'args_num' )->andReturn( 2 );
        $subject->add( $hook );
        assertTrue( HM::hasAction( 'save_post', [ $hook, 'proxy' ], 10 ) );
        $subject->remove( $hook );
        assertFalse( HM::hasAction( 'save_post', [ $hook, 'proxy' ], 10 ) );
        assertActionAdded( 'save_post', [ $hook, 'proxy' ], 10, 2 );
    }

    /**
     * @expectedException Brain\Striatum\Tests\HookException
     */
    function testRemoveWhenIsFilter() {
        $hook = \Mockery::mock( 'Brain\Striatum\Hook' );
        $subject = $this->getMocked( 'the_title' );
        $subject->shouldReceive( 'isFilter' )->atLeast( 1 )->withNoArgs()->andReturn( TRUE );
        $hook->shouldReceive( 'get' )->atLeast( 1 )->with( 'priority' )->andReturn( 20 );
        $hook->shouldReceive( 'get' )->atLeast( 1 )->with( 'args_num' )->andReturn( 3 );
        $subject->add( $hook );
        assertTrue( HM::hasFilter( 'the_title', [$hook, 'proxy' ], 20 ) );
        $subject->remove( $hook );
        assertFalse( HM::hasFilter( 'the_title', [$hook, 'proxy' ], 20 ) );
        assertFilterAdded( 'the_title', [ $hook, 'proxy' ], 20, 3 );
    }

    function testNotify() {
        $subject = $this->getMocked( 'save_post' );
        $notified = $subject->notify( [ 'foo', 'bar', 'baz' ] );
        assertNull( $notified );
        assertActionFired( 'save_post', [ 'foo', 'bar', 'baz' ] );
    }

    function testNotifyWhenIsFilter() {
        $subject = $this->getMocked( 'the_title' );
        $subject->shouldReceive( 'isFilter' )->atLeast( 1 )->withNoArgs()->andReturn( TRUE );
        $notified = $subject->notify( [ 'foo', 'bar', 'baz' ] );
        assertEquals( [ 'filter', 'the_title', [ 'foo', 'bar', 'baz' ] ], $notified );
        assertFilterFired( 'the_title', [ 'foo', 'bar', 'baz' ] );
    }

    function testSetGetContext() {
        $subject = $this->get();
        assertEquals( [ ], $subject->getContext() );
        $subject->setContext( 'foo', 'bar' );
        $subject->setContext( 'bar', 'baz' );
        $subject->setContext( 'baz' );
        assertEquals( [ 'foo' => 'bar', 'bar' => 'baz', 'baz' => NULL ], $subject->getContext() );
        assertEquals( 'bar', $subject->getContext( 'foo' ) );
        assertEquals( 'baz', $subject->getContext( 'bar' ) );
        assertNull( $subject->getContext( 'baz' ) );
        $subject->setContext();
        assertEquals( [ ], $subject->getContext() );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetContextFailsIfBadIndex() {
        $subject = $this->get();
        $subject->setContext( TRUE, 'bar' );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetContextFailsIfEmptyIndexNonEmptyValue() {
        $subject = $this->get();
        $subject->setContext( NULL, 'bar' );
    }

    function testMagicGet() {
        $subject = $this->get();
        $subject->setContext( 'foo', 'bar' );
        $subject->setContext( 'bar', 'baz' );
        assertEquals( 'bar', $subject->foo );
        assertEquals( 'baz', $subject->bar );
    }

}