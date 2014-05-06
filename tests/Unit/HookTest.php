<?php namespace Brain\Striatum\Tests\Unit;

use Brain\Striatum\Tests\TestCase;
use Brain\Striatum as S;

class HookTest extends TestCase {

    function testSetGetSubject() {
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $hook = new S\Hook;
        assertEquals( $hook, $hook->setSubject( $subject ) );
        assertEquals( $hook->getSubject(), $subject );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetFailsIfBadIndex() {
        $hook = new S\Hook;
        $hook->set( TRUE );
    }

    function testSet() {
        $hook = new S\Hook;
        assertEquals( $hook, $hook->set( 'foo', [ ] ) );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testGetFailsIfBadIndex() {
        $hook = new S\Hook;
        $hook->get( TRUE );
    }

    function testGetFullArgs() {
        $hook = new S\Hook;
        assertTrue( is_array( $hook->get() ) );
    }

    function testSetGet() {
        $hook = new S\Hook;
        assertEquals( $hook, $hook->set( 'foo', TRUE ) );
        assertEquals( TRUE, $hook->get( 'foo' ) );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetIdFailsIfBadId() {
        $hook = new S\Hook;
        $hook->setId( TRUE );
    }

    function testSetIdGetId() {
        $hook = new S\Hook;
        assertEquals( $hook, $hook->setId( 'foo' ) );
        assertEquals( 'foo', $hook->getId() );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testPrepareFailsIfBadArgs() {
        $hook = new S\Hook;
        $hook->prepare( TRUE );
    }

    function testPrepareFromString() {
        $hook = new S\Hook;
        assertEquals( $hook, $hook->prepare( 'priority=2&args_num=2&times=2&callback=__return_true' ) );
        assertTrue( $hook->get( 'callback' ) === '__return_true' );
        assertTrue( $hook->get( 'priority' ) === 2 );
        assertTrue( $hook->get( 'args_num' ) === 2 );
        assertTrue( $hook->get( 'times' ) === 2 );
        // using __call()
        assertTrue( $hook->getCallback() === '__return_true' );
        assertTrue( $hook->getPriority() === 2 );
        assertTrue( $hook->getArgs_Num() === 2 );
        assertTrue( $hook->getTimes() === 2 );
    }

    function testPrepareFromArray() {
        $hook = new S\Hook;
        $args = [ 'callback' => '__return_true', 'priority' => '2', 'args_num' => '2', 'times' => '2' ];
        assertEquals( $hook, $hook->prepare( $args ) );
        assertTrue( $hook->get( 'callback' ) === '__return_true' );
        assertTrue( $hook->get( 'priority' ) === 2 );
        assertTrue( $hook->get( 'args_num' ) === 2 );
        assertTrue( $hook->get( 'times' ) === 2 );
        // using __call()
        assertTrue( $hook->getCallback() === '__return_true' );
        assertTrue( $hook->getPriority() === 2 );
        assertTrue( $hook->getArgs_Num() === 2 );
        assertTrue( $hook->getTimes() === 2 );
    }

    function testProxyReturnFirstParamIfNotCheck() {
        $hook = \Mockery::mock( 'Brain\Striatum\Hook' )->makePartial();
        $hook->shouldReceive( 'check' )->atLeast( 1 )->withNoArgs()->andReturn( FALSE );
        assertEquals( 'foo', $hook->proxy( 'foo' ) );
        assertNull( $hook->proxy() );
    }

    function testProxy() {
        $hook = \Mockery::mock( 'Brain\Striatum\Hook' )->makePartial();
        $hook->set();
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $hook->shouldReceive( 'check' )->atLeast( 1 )->withNoArgs()->andReturn( TRUE );
        $hook->shouldReceive( 'getSubject' )->atLeast( 1 )->withNoArgs()->andReturn( $subject );
        $hook->shouldReceive( 'update' )->atLeast( 1 )->with( $subject )->andReturn( 'bar' );
        assertEquals( 'bar', $hook->proxy( 'baz' ) );
        assertEquals( [ 'baz' ], $hook->getLastArgs() );
    }

    function testProxyCutArgs() {
        $hook = \Mockery::mock( 'Brain\Striatum\Hook' )->makePartial();
        $hook->set();
        $hook->set( 'args_num', 2 );
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $hook->shouldReceive( 'check' )->atLeast( 1 )->withNoArgs()->andReturn( TRUE );
        $hook->shouldReceive( 'getSubject' )->atLeast( 1 )->withNoArgs()->andReturn( $subject );
        $hook->shouldReceive( 'update' )->atLeast( 1 )->with( $subject )->andReturn( 'bar' );
        assertEquals( 'bar', $hook->proxy( 'foo', 'bar', 'baz' ) );
        assertEquals( [ 'foo', 'bar' ], $hook->getLastArgs() );
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    function testUpdateFailsNoSubjectGiven() {
        $hook = new S\Hook;
        $hook->update( 'foo' );
    }

    function testUpdate() {
        $hook = \Mockery::mock( 'Brain\Striatum\Hook' )->makePartial();
        $hook->set();
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $subject->shouldReceive( 'isFilter' )->atLeast( 1 )->withNoArgs()->andReturn( TRUE );
        $hook->shouldReceive( 'before' )->atLeast( 1 )->with( $subject )->andReturn( $hook );
        $hook->shouldReceive( 'get' )->atLeast( 1 )->with( 'callback' )->andReturn( '__return_true' );
        $hook->shouldReceive( 'getLastArgs' )->atLeast( 1 )->withNoArgs()->andReturn( [ ] );
        $hook->shouldReceive( 'after' )->atLeast( 1 )->with( $subject )->andReturn( $hook );
        assertTrue( $hook->update( $subject ) );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetLastArgsFailsIfBadArgs() {
        $hook = new S\Hook;
        $hook->setLastArgs( 'foo' );
    }

    function testSetLastArgsGetLastArgs() {
        $hook = new S\Hook;
        assertEquals( $hook, $hook->setLastArgs( [ 'foo', 'bar' ] ) );
        assertEquals( [ 'foo', 'bar' ], $hook->getLastArgs() );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testRunNTimesFailsIfBadNum() {
        $hook = new S\Hook;
        $hook->runNTimes( '-1' );
    }

    function testRunNTimes() {
        $hook = new S\Hook;
        $hook->runNTimes( '2' );
        assertTrue( $hook->getTimes() === 2 );
    }

    /**
     * Handled by __call()
     */
    function testRunOnce() {
        $hook = new S\Hook;
        $hook->runOnce();
        assertTrue( $hook->getTimes() === 1 );
    }

    /**
     * Handled by __call()
     */
    function testRunTwice() {
        $hook = new S\Hook;
        $hook->runTwice();
        assertTrue( $hook->getTimes() === 2 );
    }

    /**
     * Handled by __call()
     */
    function testRunXTimes() {
        $hook = new S\Hook;
        $hook->run12Times();
        assertTrue( $hook->getTimes() === 12 );
    }

    function testCheckFailsIfNoSubject() {
        $hook = new S\Hook;
        $hook->setId( 'foo' )->set( 'callback', '__return_true' );
        assertFalse( $hook->check() );
    }

    function testCheckFailsIfBadCallback() {
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $hook = new S\Hook;
        $hook->setId( 'foo' )->set( 'callback', 'foo' )->setSubject( $subject );
        assertFalse( $hook->check() );
    }

    function testCheckFailsIfNoId() {
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $hook = new S\Hook;
        $hook->set( 'callback', '__return_true' )->setSubject( $subject );
        assertFalse( $hook->check() );
    }

    function testCheck() {
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $hook = new S\Hook;
        $hook->set( 'callback', '__return_true' )->setId( 'foo' )->setSubject( $subject );
        assertTrue( $hook->check() );
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    function testBeforeFailsIfNoSubjectGiven() {
        $hook = new S\Hook;
        $hook->before( 'foo' );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testBeforeFailsIfNoId() {
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $hook = new S\Hook;
        $hook->before( $subject );
    }

    function testBefore() {
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $subject->shouldReceive( 'setInfo' )->atLeast( 1 )->withAnyArgs()->andReturnNull();
        $subject->shouldReceive( 'getInfo' )->atLeast( 1 )->withAnyArgs()->andReturnNull();
        $hook = new S\Hook;
        $hook->setId( 'foo' )->set( 'callback', '__return_true' )->prepare( [ ] ); // set defaults
        assertEquals( $hook, $hook->before( $subject ) );
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    function testAfterFailsIfNoSubjectGiven() {
        $hook = new S\Hook;
        $hook->after( 'foo' );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testAfterFailsIfNoId() {
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $hook = new S\Hook;
        $hook->after( $subject );
    }

    function testAfter() {
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $subject->shouldReceive( 'setInfo' )->atLeast( 1 )->withAnyArgs()->andReturnNull();
        $subject->shouldReceive( 'getInfo' )->atLeast( 1 )->withAnyArgs()->andReturnNull();
        $hook = new S\Hook;
        $hook->setId( 'foo' )->prepare( [ ] );
        assertEquals( $hook, $hook->after( $subject ) );
    }

    function testBeforeDetachIfNeeded() {
        $hook = new S\Hook;
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $subject->shouldReceive( 'setInfo' )->atLeast( 1 )->withAnyArgs()->andReturnNull();
        $subject->shouldReceive( 'getInfo' )->atLeast( 1 )->withAnyArgs()->andReturnNull();
        $subject->shouldReceive( 'detach' )->twice()->with( $hook )->andReturn( 'detached' );
        $hook->setId( 'foo' )->prepare( [ ] )->runOnce();
        $hook->after( $subject ); // increment times count
        assertEquals( 'detached', $hook->before( $subject ) );
    }

    function testAfterDetachIfNeeded() {
        $hook = new S\Hook;
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $subject->shouldReceive( 'setInfo' )->atLeast( 1 )->withAnyArgs()->andReturnNull();
        $subject->shouldReceive( 'getInfo' )->atLeast( 1 )->withAnyArgs()->andReturnNull();
        $subject->shouldReceive( 'detach' )->atLeast( 1 )->with( $hook )->andReturn( 'detached' );
        $hook->setId( 'foo' )->prepare( [ ] )->runOnce();
        $hook->after( $subject ); // increment times count
        assertEquals( 'detached', $hook->after( $subject ) );
    }

}