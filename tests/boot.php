<?php
if ( ! defined( 'STRIATUMBASEPATH' ) ) define( 'STRIATUMBASEPATH', dirname( dirname( __FILE__ ) ) );

require_once STRIATUMBASEPATH . '/vendor/autoload.php';

if ( ! defined( 'WP_DEBUG' ) ) {
    define( 'WP_DEBUG', TRUE );
}
require_once STRIATUMBASEPATH . '/vendor/phpunit/phpunit/PHPUnit/Framework/Assert/Functions.php';

if ( ! class_exists( 'WP_Error' ) ) require_once __DIR__ . '/class-wp-error.php';

if ( ! function_exists( 'trailingslashit' ) ) require_once __DIR__ . '/wp-functions.php';

if ( ! function_exists( 'assertIsWPError' ) ) {

    function assertIsWPError( $thing = NULL ) {
        assertInstanceOf( '\WP_Error', $thing, 'Given variable is not a WP_Error instance.' );
    }

}