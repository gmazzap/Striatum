<?php
if ( ! class_exists( 'Brain\Striatum\Striatum' ) && is_file( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

if ( function_exists( 'add_action' ) ) {
    require_once __DIR__ . '/functions.php';
    add_action( 'brain_init', function( $brain ) {
        $brain->addModule( new Brain\Striatum\BrainModule );
    } );
}