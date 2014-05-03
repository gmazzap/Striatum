<?php

namespace Brain\Striatum\Tests\Unit;

use Brain\Striatum\Tests\TestCase;

class BucketTest extends TestCase {

    function testAdd() {
        $hook = \Mockery::mock( 'Brain\Striatum\HookInterface' );
        $hook->shouldReceive( 'getId' )->atLeast( 1 )->andReturn( 'foo' );
        $bucket = new \Brain\Striatum\Bucket;
        $bucket->add( $hook );
        assertEquals( $hook, $bucket['foo'] );
    }

    function testForeachReturnsHooksAdded() {
        $hook1 = \Mockery::mock( 'Brain\Striatum\HookInterface' );
        $hook1->shouldReceive( 'getId' )->atLeast( 1 )->andReturn( 'hook1' );
        $hook2 = \Mockery::mock( 'Brain\Striatum\HookInterface' );
        $hook2->shouldReceive( 'getId' )->atLeast( 1 )->andReturn( 'hook2' );
        $hook3 = \Mockery::mock( 'Brain\Striatum\HookInterface' );
        $hook3->shouldReceive( 'getId' )->atLeast( 1 )->andReturn( 'hook3' );
        $bucket = new \Brain\Striatum\Bucket;
        $bucket->add( $hook1 );
        $bucket->add( $hook2 );
        $bucket->add( $hook3 );
        $i = 0;
        foreach ( $bucket as $hook ) {
            $i ++;
            assertInstanceOf( 'Brain\Striatum\HookInterface', $hook );
            assertEquals( $hook->getId(), "hook{$i}" );
        }
    }

}