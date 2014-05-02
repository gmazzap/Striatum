<?php

namespace Brain\Striatum\Tests\Unit;

use Brain\Striatum\Tests\TestCase;

class BucketTest extends TestCase {

    function testAdd() {
        $hook = \Mockery::mock( 'Brain\Striatum\HookInterface' );
        $hook->shouldReceive( 'getId' )->once()->andReturn( 'foo' );
        $bucket = new \Brain\Striatum\Bucket;
        $bucket->add( $hook );
        assertEquals( $hook, $bucket['foo'] );
    }

}