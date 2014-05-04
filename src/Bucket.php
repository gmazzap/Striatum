<?php namespace Brain\Striatum;

class Bucket extends \ArrayObject implements BucketInterface {

    public function add( HookInterface $hook ) {
        $id = $hook->getId();
        if ( is_string( $id ) && ! empty( $id ) ) {
            $this->offsetSet( $id, $hook );
        }
        return $hook;
    }

    public function get( $id ) {
        if ( $this->offsetExists( $id ) ) {
            return $this->offsetGet( $id );
        }
    }

    public function remove( HookInterface $hook ) {
        $id = $hook->getId();
        if ( $this->offsetExists( $id ) ) {
            return $this->offsetUnset( $id );
        }
    }

}