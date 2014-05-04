<?php namespace Brain\Striatum;

trait Contextable {

    function setContext( $key, $index = NULL, $value = NULL ) {
        if ( is_null( $index ) && ! is_null( $value ) ) {
            throw new \InvalidArgumentException;
        }
        if ( is_null( $index ) && is_string( $key ) && ! empty( $key ) ) {
            $this->$key = new \ArrayObject;
        } elseif ( is_string( $index ) && ! empty( $index ) ) {
            $this->checkContextKey( $key );
            $context = $this->$key;
            $context[$index] = $value;
        } else {
            throw new \InvalidArgumentException;
        }
        return $this;
    }

    function getContext( $key, $index = NULL ) {
        $this->checkContextKey( $key );
        if ( is_null( $index ) ) {
            $context = $this->$key;
            return $context->getArrayCopy();
        } elseif ( is_string( $index ) && $this->$key->offsetExists( $index ) ) {
            $context = $this->$key;
            return $context[$index];
        } elseif ( ! is_string( $index ) || empty( $index ) ) {
            throw new \InvalidArgumentException;
        }
    }

    function unsetContext( $key, $index = NULL ) {
        $this->checkContextKey( $key );
        if ( is_null( $index ) ) {
            $this->resetContext( $key );
        } elseif ( is_string( $index ) && $this->$key->offsetExists( $index ) ) {
            $context = $this->$key;
            unset( $context[$index] );
        }
        return $this;
    }

    function resetContext( $key ) {
        $this->setContext( $key );
        return $this;
    }

    function checkContextKey( $key ) {
        if ( ! is_string( $key ) || ! isset( $this->$key ) || ! $this->$key instanceof \ArrayObject ) {
            throw new \InvalidArgumentException;
        }
    }

}