<?php namespace Brain\Striatum\Tests\Unit;

use Brain\Striatum\Tests\TestCase;
use Brain\HooksMock\HooksMock as HM;
use Brain\Striatum as S;

class SubjectTest extends TestCase {

    private function get() {
        $bucket = \Mockery::mock( 'Brain\Striatum\Bucket' );
        return new S\Subject( $bucket );
    }

    private function getMocked( $id = NULL ) {
        $bucket = \Mockery::mock( 'Brain\Striatum\Bucket' );
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' )->makePartial();
        $subject->shouldReceive( 'getHooks' )->withNoArgs()->andReturn( $bucket );
        $subject->setInfo();
        if ( ! is_null( $id ) ) {
            $subject->shouldReceive( 'getId' )->withNoArgs()->andReturn( $id );
        }
        return $subject;
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    function testAttachFailsIfNoHookGiven() {
        $subject = $this->get()->attach();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testAttachFailsIfBadHookGiven() {
        $observer = \Mockery::mock( 'SplObserver' );
        $this->get()->attach( $observer );
    }

    /**
     * @expectedException DomainException
     */
    function testAttachFailsIfBadHookNotPassCheck() {
        $hook = \Mockery::mock( 'Brain\Striatum\Hook' );
        $hook->shouldReceive( 'check' )->atLeast( 1 )->withNoArgs()->andReturn( FALSE );
        $this->getMocked()->attach( $hook );
    }

    function testAttach() {
        $hook = \Mockery::mock( 'Brain\Striatum\Hook' );
        $hook->shouldReceive( 'set' )->atLeast( 1 )->withAnyArgs()->andReturnNull();
        $hook->shouldReceive( 'check' )->atLeast( 1 )->withNoArgs()->andReturn( TRUE );
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
     * @expectedException Brain\HooksMock\HookException
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
     * @expectedException Brain\HooksMock\HookException
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
        assertEquals( [ ], $subject->getInfo() );
        $subject->setInfo( 'foo', 'bar' );
        $subject->setInfo( 'bar', 'baz' );
        $subject->setInfo( 'baz' );
        assertEquals( [ 'foo' => 'bar', 'bar' => 'baz', 'baz' => NULL ], $subject->getInfo() );
        assertEquals( 'bar', $subject->getInfo( 'foo' ) );
        assertEquals( 'baz', $subject->getInfo( 'bar' ) );
        assertNull( $subject->getInfo( 'baz' ) );
        $subject->resetContext( 'context' );
        assertEquals( [ ], $subject->getInfo() );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetContextFailsIfBadIndex() {
        $subject = $this->get();
        $subject->setInfo( TRUE, 'bar' );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetContextFailsIfEmptyIndexNonEmptyValue() {
        $subject = $this->get();
        $subject->setInfo( NULL, 'bar' );
    }

    function testMagicGet() {
        $subject = $this->get();
        $subject->setInfo( 'foo', 'bar' );
        $subject->setInfo( 'bar', 'baz' );
        assertEquals( 'bar', $subject->foo );
        assertEquals( 'baz', $subject->bar );
    }

}