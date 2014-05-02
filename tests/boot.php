<?php
if ( ! defined( 'STRIATUMBASEPATH' ) ) define( 'STRIATUMBASEPATH', dirname( dirname( __FILE__ ) ) );
$autoload = require_once STRIATUMBASEPATH . '/vendor/autoload.php';
$autoload->add( 'Brain\\Striatum\\Tests\\', __DIR__ );
require_once STRIATUMBASEPATH . '/vendor/phpunit/phpunit/PHPUnit/Framework/Assert/Functions.php';
if ( ! class_exists( 'WP_Error' ) ) require_once __DIR__ . '/class-wp-error.php';
require_once __DIR__ . '/wp-functions.php';
