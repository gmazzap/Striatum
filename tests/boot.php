<?php
if ( ! defined( 'STRIATUMBASEPATH' ) ) define( 'STRIATUMBASEPATH', dirname( dirname( __FILE__ ) ) );

require_once STRIATUMBASEPATH . '/vendor/autoload.php';

if ( ! defined( 'WP_DEBUG' ) ) {
    define( 'WP_DEBUG', TRUE );
}
require_once STRIATUMBASEPATH . '/vendor/phpunit/phpunit/PHPUnit/Framework/Assert/Functions.php';

if ( ! class_exists( 'WP_Error' ) ) require_once __DIR__ . '/class-wp-error.php';

if ( ! function_exists( 'add_action' ) ) require_once __DIR__ . '/wp-functions.php';

if ( ! function_exists( 'assertActionAdded' ) ) {

    function assertActionAdded( $hook = '', $callback = NULL, $priority = NULL, $args_num = NULL ) {
        Brain\Striatum\Tests\HooksMock::assertActionAdded( $hook, $callback, $priority, $args_num );
    }

}

if ( ! function_exists( 'assertFilterAdded' ) ) {

    function assertFilterAdded( $hook = '', $callback = NULL, $priority = NULL, $args_num = NULL ) {
        Brain\Striatum\Tests\HooksMock::assertFilterAdded( $hook, $callback, $priority, $args_num );
    }

}

if ( ! function_exists( 'assertActionFired' ) ) {

    function assertActionFired( $hook = '', $args = NULL ) {
        Brain\Striatum\Tests\HooksMock::assertActionFired( $hook, $args );
    }

}

if ( ! function_exists( 'assertFilterFired' ) ) {

    function assertFilterFired( $hook = '', $args = NULL ) {
        Brain\Striatum\Tests\HooksMock::assertFilterFired( $hook, $args );
    }

}

if ( ! function_exists( 'assertIsWPError' ) ) {

    function assertIsWPError( $thing ) {
        assertTrue( is_wp_error( $thing ), 'Given variable is not a WP_Error instance.' );
    }

}
