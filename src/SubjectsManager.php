<?php

namespace Brain\Striatum;

class SubjectsManager implements SubjectsManagerInterface {

    protected $subjects;

    protected $frozen;

    protected $prototype;

    function __construct( \ArrayObject $subjects, \ArrayObject $frozen, SubjectInterface $si ) {
        $this->subjects = $subjects;
        $this->frozen = $frozen;
        $this->prototype = $si;
    }

    function getSubject( $id ) {
        return $this->getSubjectOrFrozen( $id, FALSE );
    }

    function getFrozenSubject( $id ) {
        return $this->getSubjectOrFrozen( $id, TRUE );
    }

    function addSubject( $id, $is_filter = FALSE ) {
        $subject = $this->subjectGetOrCreate( $id, FALSE );
        $subject->isFilter( $is_filter );
        return $subject;
    }

    function removeSubject( $id ) {
        $hooks = $this->getSubject( $id );
        if ( $hooks instanceof SubjectInterface ) {
            $hooks->detachAll();
            unset( $this->subjects[$id] );
        }
    }

    function freezeSubject( $id ) {
        $hooks = $this->getSubject( $id );
        if ( $hooks instanceof SubjectInterface ) {
            $this->frozen[$id] = $hooks;
            unset( $this->subjects[$id] );
            $hooks->removeAll();
        }
    }

    function unfreezeSubject( $id ) {
        $hooks = $this->getFrozenSubject( $id );
        if ( is_array( $hooks ) ) {
            $this->subjects[$id] = $hooks;
            unset( $this->frozen[$id] );
            $hooks->restoreAll();
        }
    }

    function addSubjects( $ids = [ ], $is_filter = FALSE ) {
        return $this->parseSubjects( $ids, 'add', $is_filter );
    }

    function removeSubjects( $ids = [ ] ) {
        return $this->parseSubjects( $ids, 'remove' );
    }

    function freezeSubjects( $ids = [ ] ) {
        return $this->parseSubjects( $ids, 'freeze' );
    }

    function unfreezeSubjects( $ids = [ ] ) {
        return $this->parseSubjects( $ids, 'unfreeze' );
    }

    function getSubjectOrFrozen( $id, $frozen = FALSE ) {
        $id = $this->checkSubjectId( $id );
        $target = $frozen === TRUE ? $this->frozen : $this->subjects;
        if ( ! isset( $target[$id] ) ) {
            return;
        } elseif ( ! $target[$id] instanceof SubjectInterface ) {
            throw new \UnexpectedValueException;
        }
        return $target[$id];
    }

    protected function subjectGetOrCreate( $id = '' ) {
        $exists = $this->getSubject( $id );
        if ( is_null( $exists ) ) {
            $this->subjects[$id] = clone $this->prototype;
            $this->subjects[$id]->setId( $id );
        } else {
            $this->subjects[$id] = $exists;
        }
        return $this->subjects[$id];
    }

    protected function parseSubjects( $ids = [ ], $action = '', $opt = NULL ) {
        if ( ! in_array( $action, [ 'add', 'remove', 'freeze', 'unfreeze' ], TRUE ) ) {
            throw new \InvalidArgumentException;
        }
        $ids = $this->checkSubjectIds( $ids );
        $done = [ ];
        foreach ( $ids as $id ) {
            $args = [ $id ];
            if ( ! is_null( $opt ) ) {
                $args = is_array( $opt ) ? array_merge( $args, $opt ) : [ $id, $opt ];
            }
            $done[] = call_user_func_array( [ $this, "{$action}Subject" ], $args );
        }
        return $done;
    }

    protected function checkSubjectId( $id ) {
        if ( empty( $id ) || ! is_string( $id ) ) {
            throw new \InvalidArgumentException;
        }
        return $id;
    }

    protected function checkSubjectIds( $ids ) {
        if ( is_string( $ids ) ) {
            $ids = explode( ',', $ids );
        }
        if ( empty( $ids ) || ! is_array( $ids ) ) {
            throw new \InvalidArgumentException;
        }
        return $ids;
    }

}