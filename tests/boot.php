<?php
if ( ! defined( 'STRIATUMBASEPATH' ) ) define( 'STRIATUMBASEPATH', dirname( dirname( __FILE__ ) ) );
$autoload = require_once STRIATUMBASEPATH . '/vendor/autoload.php';
if ( ! defined( 'WP_DEBUG' ) ) {
    define( 'WP_DEBUG', TRUE );
}
require_once STRIATUMBASEPATH . '/vendor/phpunit/phpunit/PHPUnit/Framework/Assert/Functions.php';
if ( ! class_exists( 'WP_Error' ) ) require_once __DIR__ . '/class-wp-error.php';
require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/HooksMock.php';
require_once __DIR__ . '/wp-functions.php';

if ( ! function_exists( 'assertActionAdded' ) ) {

    function assertActionAdded( $hook = '', $cb = NULL, $pri = NULL, $n_args = NULL ) {
        Brain\Striatum\Tests\HooksMock::assertActionAdded( $hook, $cb, $pri, $n_args );
    }

}

if ( ! function_exists( 'assertFilterAdded' ) ) {

    function assertFilterAdded( $hook = '', $cb = NULL, $pri = NULL, $n_args = NULL ) {
        Brain\Striatum\Tests\HooksMock::assertFilterAdded( $hook, $cb, $pri, $n_args );
    }

}

if ( ! function_exists( 'assertActionFired' ) ) {

    function assertActionFired( $hook = '', $cb = NULL ) {
        Brain\Striatum\Tests\HooksMock::assertActionFired( $hook, $cb );
    }

}

if ( ! function_exists( 'assertFilterFired' ) ) {

    function assertFilterFired( $hook = '', $cb = NULL ) {
        Brain\Striatum\Tests\HooksMock::assertFilterFired( $hook, $cb );
    }

}
