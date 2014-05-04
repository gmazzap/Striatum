<?php namespace Brain\Striatum;

use Brain\Container as Container;
use Brain\Module as Module;

class BrainModule implements Module {

    function getBindings( Container $c ) {
        $c["striatum.hooks"] = function() {
            return new \ArrayObject;
        };
        $c["striatum.frozen"] = function() {
            return new \ArrayObject;
        };
        $c["striatum.bucket"] = $c->factory( function() {
            return new Bucket();
        } );
        $c["striatum.subject"] = $c->factory( function($c) {
            return new Subject( $c["striatum.bucket"] );
        } );
        $c["striatum.hook"] = $c->factory( function() {
            return new Hook;
        } );
        $c["striatum.manager"] = function($c) {
            return new SubjectsManager(
                $c["striatum.hooks"], $c["striatum.frozen"], $c["striatum.subject"]
            );
        };
    }

    function boot( Container $c ) {
        return;
    }

    function getPath() {
        return trailingslashit( dirname( plugin_dir_path( __FILE__ ) ) );
    }

}