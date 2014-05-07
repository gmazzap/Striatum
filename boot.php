<?php
if ( ! class_exists( 'Brain\Striatum\BrainModule' ) && is_file( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

if ( function_exists( 'add_action' ) ) {

    add_action( 'brain_init', function( $brain ) {
        $brain->addModule( new Brain\Striatum\BrainModule );
    } );

    add_action( 'brain_loaded', function() {
        require_once __DIR__ . '/Hooks.php';
    }, 0 );
}