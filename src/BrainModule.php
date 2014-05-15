<?php namespace Brain\Striatum;

use Brain\Container;
use Brain\Module;

/**
 * Brain module implementation
 *
 * @package Brain\Striatum
 * @see https://github.com/Giuseppe-Mazzapica/Brain
 */
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
        $c["hooks.api"] = function($c) {
            return new API(
                $c["striatum.manager"], $c["striatum.hooks"], $c["striatum.hook"]
            );
        };
    }

    function boot( Container $c ) {
        return;
    }

    function getPath() {
        return trailingslashit( dirname( dirname( __FILE__ ) ) );
    }

}