<?php namespace Brain\Striatum;

trait Idable {

    public function setId( $id = NULL ) {
        if ( ! is_string( $id ) || empty( $id ) ) {
            throw new \InvalidArgumentException;
        }
        $this->id = $id;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

}