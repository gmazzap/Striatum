<?php
if ( ! function_exists( 'stripslashes_deep' ) ) {

    function stripslashes_deep( $value ) {
        if ( is_array( $value ) ) {
            $value = array_map( 'stripslashes_deep', $value );
        } elseif ( is_object( $value ) ) {
            $vars = get_object_vars( $value );
            foreach ( $vars as $key => $data ) {
                $value->{$key} = stripslashes_deep( $data );
            }
        } elseif ( is_string( $value ) ) {
            $value = stripslashes( $value );
        }
        return $value;
    }

}

if ( ! function_exists( 'wp_parse_str' ) ) {

    function wp_parse_str( $string, &$array ) {
        parse_str( $string, $array );
        if ( get_magic_quotes_gpc() ) $array = stripslashes_deep( $array );
    }

}

if ( ! function_exists( 'wp_parse_args' ) ) {

    function wp_parse_args( $args, $defaults = '' ) {
        if ( is_object( $args ) ) $r = get_object_vars( $args );
        elseif ( is_array( $args ) ) $r = & $args;
        else wp_parse_str( $args, $r );
        if ( is_array( $defaults ) ) return array_merge( $defaults, $r );
        return $r;
    }

}

if ( ! function_exists( '__return_false' ) ) {

    function __return_false() {
        return FALSE;
    }

}

if ( ! function_exists( '__return_true' ) ) {

    function __return_true() {
        return TRUE;
    }

}

if ( ! function_exists( '__return_empty_string' ) ) {

    function __return_empty_string() {
        return '';
    }

}
