<?php namespace Brain\Striatum;

interface HookInterface {

    public function setId( $id );

    function getId();

    function setSubject( SubjectInterface $subject );

    function getSubject();

    function get( $index = NULL );

    function set( $index = NULL, $value = NULL );

    function prepare( $args );

    function proxy();
}