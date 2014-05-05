<?php namespace Brain\Striatum;

trait Fullclonable {

    public function __clone() {
        foreach ( array_keys( get_object_vars( $this ) ) as $var ) {
            if ( is_object( $this->$var ) ) {
                $this->$var = clone $this->$var;
            }
        }
    }

}