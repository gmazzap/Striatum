<?php

namespace Brain\Striatum;

class Hook implements HookInterface, \SplObserver {

    private $times = 0;

    private $storage;

    function __construct() {
        $base = [ 'args' => [ ], 'id' => NULL, 'subject' => NULL, 'last_args' => NULL ];
        $this->storage = new \ArrayObject( $base );
    }

    public function __get( $name ) {
        return $this->get( $name );
    }

    public function __call( $name, $arguments ) {
        if ( strpos( $name, 'get' ) === 0 && empty( $arguments ) ) {
            $name = strtolower( substr( $name, 3 ) );
            return $this->get( $name );
        } elseif ( preg_match( '/^run([0-9]+)+times$/i', $name, $matches ) ) {
            return $this->runNTimes( $matches[1] );
        } elseif ( strpos( $name, 'run' ) === 0 ) {
            $times = strtolower( ltrim( $name, 'run' ) );
            if ( in_array( $times, [ 'once', 'twice' ], TRUE ) ) {
                $n = $times === 'twice' ? 2 : 1;
                return $this->runNTimes( $n );
            }
        }
    }

    public function setSubject( SubjectInterface $subject ) {
        $this->storage['subject'] = $subject;
        return $this;
    }

    public function getSubject() {
        return $this->storage['subject'];
    }

    public function set( $index, $value = NULL ) {
        if ( ! is_string( $index ) ) {
            throw new \InvalidArgumentException;
        }
        $this->storage['args'][$index] = $value;
        return $this;
    }

    public function get( $index = NULL ) {
        if ( ! is_null( $index ) && ! is_string( $index ) ) {
            throw new \InvalidArgumentException;
        }
        if ( is_null( $index ) ) {
            return $this->storage['args'];
        } elseif ( isset( $this->storage['args'][$index] ) ) {
            return $this->storage['args'][$index];
        }
    }

    public function setId( $id ) {
        if ( ! is_string( $id ) || empty( $id ) ) {
            throw new \InvalidArgumentException;
        }
        $this->storage['id'] = $id;
        return $this;
    }

    public function getId() {
        return $this->storage['id'];
    }

    public function prepare( $args ) {
        if ( ! is_array( $args ) && ! is_string( $args ) ) {
            throw new \InvalidArgumentException;
        }
        $def = [ 'callback' => '__return_false', 'priority' => 10, 'args_num' => 1, 'times' => 0 ];
        $this->storage['args'] = wp_parse_args( $args, $def );
        return $this;
    }

    public function proxy() {
        if ( ! $this->check() ) {
            return func_num_args() > 0 ? func_get_arg( 0 ) : NULL;
        }
        $this->set( 'last_args', func_get_args() );
        return $this->update( $this->getSubject() );
    }

    public function update( \SplSubject $subject ) {
        $this->before( $subject );
        $update = call_user_func_array( $this->get( 'callback' ), $this->get( 'last_args' ) );
        $this->after( $subject );
        if ( $subject->isFilter() ) {
            return $update;
        }
    }

    public function runNTimes( $n = 1 ) {
        $this->set( 'times', absint( $n ) );
        return $this;
    }

    protected function check() {
        $sub = $this->getSubject() instanceof SubjectInterface;
        $id = $this->getId() ? : FALSE;
        return $sub && is_string( $id ) && is_callable( $this->get( 'callback' ) );
    }

    protected function before( SubjectInterface $subject ) {
        $times = $this->get( 'times' );
        if ( ( $times > 0 ) && $times >= $this->times ) {
            return $subject->detach( $this );
        }
        $subject->setContext( 'priority_now', $this->get( 'priority' ) );
        $subject->setContext( 'callback_now', $this->getID() );
        $calling = $subject->getContext( 'calling' ) ? : [ ];
        $subject->setContext( 'calling', array_merge( $calling, [ $this->getID() ] ) );
    }

    protected function after( SubjectInterface $subject ) {
        $calling = $subject->getContext( 'calling' );
        $called = $subject->getContext( 'called' ) ? : [ ];
        $subject->setContext( 'calling', array_diff( $calling, [ $this->getID() ] ) );
        $subject->setContext( 'priority_now', NULL );
        $subject->setContext( 'callback_now', NULL );
        $subject->setContext( 'called', array_merge( $called, [ $this->getID() ] ) );
        $subject->setContext( 'last_callback', $this->getID() );
        $this->times ++;
        $times = $this->get( 'times' );
        if ( ( $times > 0 ) && $this->times >= $times ) {
            $subject->detach( $this );
        }
    }

}