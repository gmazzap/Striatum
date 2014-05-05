<?php namespace Brain\Striatum\Tests\Unit;

use Brain\Striatum\Tests\TestCase;

class SubjectsManagerTest extends TestCase {

    protected function getMocked( $id = '', $filter = FALSE ) {
        $bucket = \Mockery::mock( 'Brain\Striatum\Bucket' );
        $prototype = \Mockery::mock( 'Brain\Striatum\Subject' );
        $prototype->shouldReceive( 'getHooks' )->andReturn( $bucket );
        $prototype->shouldReceive( 'setId' )->andReturn( $prototype );
        $prototype->shouldReceive( 'getId' )->andReturn( $id );
        $prototype->shouldReceive( 'isFilter' )->andReturn( $filter );
        $manager = \Mockery::mock( 'Brain\Striatum\SubjectsManager' )->makePartial();
        $manager->setContext( 'subjects' );
        $manager->setContext( 'frozen' );
        $manager->shouldReceive( 'getPrototype' )->andReturn( $prototype );
        return $manager;
    }

    /**
     * @expectedException \DomainException
     */
    function testGetSubjectFailIfNoSubjectReturned() {
        $manager = $this->getMocked();
        $manager->shouldReceive( 'getSubjects' )->atLeast( 1 )->with( 'foo' )->andReturn( 'bar' );
        $manager->getSubject( 'foo' );
    }

    /**
     * @expectedException \DomainException
     */
    function testGetFrozenSubjectFailIfNoSubjectReturned() {
        $manager = $this->getMocked();
        $manager->shouldReceive( 'getFrozen' )->atLeast( 1 )->with( 'foo' )->andReturn( 'bar' );
        $manager->getFrozenSubject( 'foo' );
    }

    function testSetSubjectGetSubject() {
        $manager = $this->getMocked();
        assertEquals( [ ], $manager->getSubjects() );
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $result = $manager->setSubjects( 'foo', $subject );
        assertEquals( $subject, $manager->getSubjects( 'foo' ) );
        assertEquals( $manager, $result );
    }

    function testSetFrozenGetFrozen() {
        $manager = $this->getMocked();
        assertEquals( [ ], $manager->getFrozen() );
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $result = $manager->setFrozen( 'foo', $subject );
        assertEquals( $subject, $manager->getFrozen( 'foo' ) );
        assertEquals( $manager, $result );
    }

    function testAddSubject() {
        $manager = $this->getMocked( 'foo' );
        $subject = $manager->addSubject( 'foo' );
        assertInstanceOf( 'Brain\Striatum\Subject', $subject );
        $manager->getSubjects( 'foo' );
        assertEquals( $subject, $manager->getSubject( 'foo' ) );
    }

    function testRemovesubject() {
        $manager = $this->getMocked();
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $subject->shouldReceive( 'detachAll' )->once()->withNoArgs()->andReturnNull();
        $manager->shouldReceive( 'getSubject' )->once()->with( 'foo' )->andReturn( $subject );
        $manager->shouldReceive( 'unsetSubjects' )->once()->with( 'foo' )->andReturnNull();
        $manager->removeSubject( 'foo' );
    }

    function testFreezeSubject() {
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $subject->shouldReceive( 'removeAll' )->once()->withNoArgs()->andReturnNull();
        $manager = $this->getMocked();
        $manager->shouldReceive( 'getSubject' )->once()->with( 'foo' )->andReturn( $subject );
        $manager->shouldReceive( 'setFrozen' )->once()->with( 'foo', $subject )->andReturn( $manager );
        $manager->shouldReceive( 'unsetSubjects' )->once()->with( 'foo' )->andReturnNull();
        assertEquals( $subject, $manager->freezeSubject( 'foo' ) );
    }

    function testUnfreezeSubject() {
        $subject = \Mockery::mock( 'Brain\Striatum\Subject' );
        $subject->shouldReceive( 'restoreAll' )->once()->withNoArgs()->andReturnNull();
        $manager = $this->getMocked();
        $manager->shouldReceive( 'getFrozenSubject' )->once()->with( 'foo' )->andReturn( $subject );
        $manager->shouldReceive( 'setSubjects' )->once()->with( 'foo', $subject )->andReturn( $manager );
        $manager->shouldReceive( 'unsetFrozen' )->once()->with( 'foo' )->andReturnNull();
        assertEquals( $subject, $manager->unfreezeSubject( 'foo' ) );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testParseSubjectsFailsIfBadAction() {
        $manager = $this->getMocked();
        $manager->parseSubjects( [ ], 'foo', NULL );
    }

    function testParseSubjectsWithNoOpt() {
        $manager = $this->getMocked();
        $ids = [ 'foo', 'bar' ];
        $manager->shouldReceive( 'checkSubjectIds' )->once()->with( 'foo,bar' )->andReturn( $ids );
        $manager->shouldReceive( 'addSubject' )->once()->with( 'foo' )->andReturn( 'bar' );
        $manager->shouldReceive( 'addSubject' )->once()->with( 'bar' )->andReturn( 'baz' );
        assertEquals( [ 'bar', 'baz' ], $manager->parseSubjects( 'foo,bar', 'add', NULL ) );
    }

    function testParseSubjectsWithSingleOpt() {
        $manager = $this->getMocked();
        $ids = [ 'foo', 'bar' ];
        $manager->shouldReceive( 'checkSubjectIds' )->once()->with( 'foo,bar' )->andReturn( $ids );
        $manager->shouldReceive( 'removeSubject' )
            ->once()
            ->with( 'foo', 'baz' )
            ->andReturn( 'one' );
        $manager->shouldReceive( 'removeSubject' )
            ->once()
            ->with( 'bar', 'baz' )
            ->andReturn( 'two' );
        assertEquals( [ 'one', 'two' ], $manager->parseSubjects( 'foo,bar', 'remove', 'baz' ) );
    }

    function testParseSubjectsWithArrayOpt() {
        $manager = $this->getMocked();
        $ids = [ 'foo', 'bar' ];
        $manager->shouldReceive( 'checkSubjectIds' )->once()->with( 'foo,bar' )->andReturn( $ids );
        $manager->shouldReceive( 'freezeSubject' )
            ->once()
            ->with( 'foo', 'baz', TRUE )
            ->andReturn( 'one' );
        $manager->shouldReceive( 'freezeSubject' )
            ->once()
            ->with( 'bar', 'baz', TRUE )
            ->andReturn( 'two' );
        $parse = $manager->parseSubjects( 'foo,bar', 'freeze', [ 'baz', TRUE ] );
        assertEquals( [ 'one', 'two' ], $parse );
    }

}