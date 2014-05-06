<?php namespace Brain;

use \Brain\Container as Brain;

/**
 * Hooks Class
 *
 * ##############################################################################################
 * Striatum package allows an object-oriented way to handle WordPress hooks.
 * ##############################################################################################
 *
 * It uses an implementation of Observer pattern, where every hook is associated to a Subject
 * instance, that notify all the Observer instances attached: so where WP core plugin API uses
 * callbacks and global variables, this package uses objects.
 *
 * This class is a sort of *proxy* to ease the package API calls.
 * All the API functions are defined in the class Brain\Striatum\API and can be called using this
 * class static methods, like:
 *
 *     Brain\Hooks::addAction( $id, $hook, $callback, $priority );
 *
 * Same methods can be also called using dynamic methods:
 *
 *     $api = new Brain\Hooks();
 *     $api->addAction( $id, $hook, $callback, $priority );
 *
 * This is useful when the package is used inside OOP plugins, making use of dependency injection.
 *
 * @package Brain\Striatum
 * @version 0.1
 */
class Hooks {

    private static $container;

    public static function setContainer( Brain $container ) {
        self::$container = $container;
        return self::$container;
    }

    public static function api() {
        return self::$container->get( 'hooks.api' );
    }

    public static function __callStatic( $name, $arguments ) {
        if ( ! self::$container instanceof Brain || ! self::api() instanceof Striatum\API ) {
            return new \WP_Error( 'hooks-api-not-ready', 'Hooks API object is not ready.' );
        }
        if ( method_exists( self::api(), $name ) ) {
            return call_user_func_array( [ self::api(), $name ], $arguments );
        } else {
            return new \WP_Error( 'hooks-api-invalid-call', 'Invalid hooks API call.' );
        }
    }

    public function __call( $name, $arguments ) {
        return self::__callStatic( $name, $arguments );
    }

}