<?php namespace Brain;

class Hooks {

    private $api;

    public static function setApi( Striatum\API $api ) {
        self::$api = $api;
        return self::$api;
    }

    public static function __callStatic( $name, $arguments ) {
        if ( ! self::$api instanceof Striatum\API ) {
            return new \WP_Error( 'hooks-api-not-ready', 'Hooks API object is not ready.' );
        }
        if ( method_exists( self::$api, $name ) ) {
            return call_user_func_array( [ self::$api, $name ], $arguments );
        } else {
            return new \WP_Error( 'hooks-api-invalid-call', 'Invalid hooks API call.' );
        }
    }

    public function __call( $name, $arguments ) {
        return self::__callStatic( $name, $arguments );
    }

}