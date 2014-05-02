<?php

namespace Brain\Striatum;

interface HookInterface {

    public function setId( $id );

    function getId();

    function get( $index = NULL );

    function set( $index, $value = NULL );

    function setSubject( SubjectInterface $subject );

    function getSubject();

    function prepare( $args );

    function proxy();
}