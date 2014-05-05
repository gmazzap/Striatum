<?php namespace Brain\Striatum\Tests\Functional;

use Brain\Hooks as H;

class APITest extends \Brain\Striatum\Tests\FunctionalTestCase {

    function testGetHooksFailsIfBadHook() {
        assertTrue( is_wp_error( H::getHooks( TRUE ) ) );
    }

    function testGetHooksFailsIfHookNotExists() {
        assertTrue( is_wp_error( H::getHooks( 'foo' ) ) );
    }

    function testGetHooks() {
        $stub = new \Brain\Striatum\Tests\ContextStub();
        H::addAction( 'init.h', 'init', '__return_true', 10, 2, 1 );
        H::addAction( 'save_post.h', 'save_post', '__return_true', 20, 3, 2 );
        H::addAction( 'wp_head.h', 'wp_head', [ __CLASS__, __FUNCTION__ ], 30, 4, 3 );
        H::addFilter( 'the_title.h', 'the_title', '__return_empty_string', 40, 5, 4 );
        H::addFilter( 'the_content.h', 'the_content', [ $stub, 'setContext' ], 50, 6, 5 );
        $cbs = [
            'init.h'        => '__return_true',
            'save_post.h'   => '__return_true',
            'wp_head.h'     => [ __CLASS__, __FUNCTION__ ],
            'the_title.h'   => '__return_empty_string',
            'the_content.h' => [ $stub, 'setContext' ]
        ];
        $t = 0;
        foreach ( [ 'init', 'save_post', 'wp_head', 'the_title', 'the_content' ] as $h ) {
            $hooks = H::getHooks( $h );
            assertTrue( is_array( $hooks ) );
            $t ++;
            foreach ( $hooks as $i => $hook ) {
                assertInstanceOf( '\Brain\Striatum\HookInterface', $hook );
                assertEquals( $h . '.h', $hook->getId() );
                assertEquals( $t + 1, $hook->args_num );
                assertEquals( $t, $hook->times );
                assertEquals( $cbs[$i], $hook->callback );
                assertEquals( $t * 10, $hook->priority );
            }
        }
    }

}