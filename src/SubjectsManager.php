<?php namespace Brain\Striatum;

class SubjectsManager implements SubjectsManagerInterface {

    use \Brain\Contextable;

    protected $subjects;

    protected $frozen;

    protected $prototype;

    public function __construct( \ArrayObject $subjects, \ArrayObject $frozen, SubjectInterface $si ) {
        $this->subjects = $subjects;
        $this->frozen = $frozen;
        $this->prototype = $si;
    }

    public function getSubject( $id ) {
        $subject = $this->getSubjects( $id );
        if ( ! is_null( $subject ) && ! $subject instanceof SubjectInterface ) {
            throw new \DomainException;
        }
        return $subject;
    }

    public function getFrozenSubject( $id ) {
        $subject = $this->getFrozen( $id );
        if ( ! is_null( $subject ) && ! $subject instanceof SubjectInterface ) {
            throw new \DomainException;
        }
        return $subject;
    }

    public function addSubject( $id, $is_filter = FALSE ) {
        $subject = $this->getSubjects( $id );
        if ( is_null( $subject ) ) {
            $prototype = $this->getPrototype();
            $subject = clone $prototype;
            $subject->setId( $id )->isFilter( $is_filter );
            $this->setSubjects( $id, $subject );
        }
        return $subject;
    }

    public function removeSubject( $id ) {
        $subject = $this->getSubject( $id );
        if ( $subject instanceof SubjectInterface ) {
            $subject->detachAll();
            $this->unsetSubjects( $id );
        }
    }

    public function freezeSubject( $id ) {
        $subject = $this->getSubject( $id );
        if ( $subject instanceof SubjectInterface ) {
            $this->setFrozen( $id, $subject );
            $subject->removeAll();
            $this->unsetSubjects( $id );
        }
        return $subject;
    }

    public function unfreezeSubject( $id ) {
        $subject = $this->getFrozenSubject( $id );
        if ( $subject instanceof SubjectInterface ) {
            $subject->restoreAll();
            $this->setSubjects( $id, $subject );
            $this->unsetFrozen( $id );
        }
        return $subject;
    }

    public function addSubjects( $ids = [ ], $is_filter = FALSE ) {
        return $this->parseSubjects( $ids, 'add', $is_filter );
    }

    public function removeSubjects( $ids = [ ] ) {
        return $this->parseSubjects( $ids, 'remove' );
    }

    public function freezeSubjects( $ids = [ ] ) {
        return $this->parseSubjects( $ids, 'freeze' );
    }

    public function unfreezeSubjects( $ids = [ ] ) {
        return $this->parseSubjects( $ids, 'unfreeze' );
    }

    public function getSubjects( $id = NULL ) {
        return $this->getContext( 'subjects', $id );
    }

    public function setSubjects( $id = NULL, SubjectInterface $subject = NULL ) {
        return $this->setContext( 'subjects', $id, $subject );
    }

    public function unsetSubjects( $id ) {
        return $this->unsetContext( 'subjects', $id );
    }

    public function getFrozen( $id = NULL ) {
        return $this->getContext( 'frozen', $id );
    }

    public function setFrozen( $id = NULL, SubjectInterface $subject = NULL ) {
        return $this->setContext( 'frozen', $id, $subject );
    }

    public function getPrototype() {
        return $this->prototype;
    }

    public function unsetFrozen( $id ) {
        return $this->unsetContext( 'frozen', $id );
    }

    public function parseSubjects( $ids = [ ], $action = '', $opt = NULL ) {
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

    public function checkSubjectIds( $ids ) {
        if ( is_string( $ids ) ) {
            $ids = array_map( 'trim', explode( '|', $ids ) );
        }
        if ( empty( $ids ) || ! is_array( $ids ) ) {
            throw new \InvalidArgumentException;
        }
        return $ids;
    }

}