<?php

namespace Brain\Striatum;

interface SubjectsManagerInterface {

    function getSubject( $id );

    function getFrozenSubject( $id );

    function addSubject( $id, $is_filter = FALSE );

    function addSubjects( $ids = [ ], $is_filter = FALSE );

    function removeSubject( $id );

    function removeSubjects( $ids = [ ] );

    function freezeSubject( $id );

    function freezeSubjects( $ids = [ ] );

    function unfreezeSubject( $id );

    function unfreezeSubjects( $ids = [ ] );
}