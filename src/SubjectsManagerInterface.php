<?php namespace Brain\Striatum;

interface SubjectsManagerInterface {

    function getSubject( $id );

    function getFrozenSubject( $id );

    function addSubject( $id, $is_filter = FALSE );

    function removeSubject( $id );

    function freezeSubject( $id );

    function unfreezeSubject( $id );
}