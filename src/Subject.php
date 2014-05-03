<?php

namespace Brain\Striatum;

class Subject implements SubjectInterface, \SplSubject {

    protected $id;

    protected $args = [ ];

    protected $hooks;

    protected $filter = FALSE;

    protected $context;

    public function __construct( BucketInterface $bucket ) {
        $this->hooks = $bucket;
        $this->context = new \ArrayObject;
    }

    public function __clone() {
        $this->hooks = clone $this->hooks;
    }

    public function __get( $name ) {
        if ( $this->context->offsetExists( $name ) ) {
            return $this->context[$name];
        }
    }

    public function attach( \SplObserver $hook ) {
        if ( ! $hook instanceof HookInterface ) {
            throw new \InvalidArgumentException;
        }
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $debug = debug_backtrace( 0, 7 );
            $hook->set( 'debug', array_slice( $debug, 2 ) );
        }
        return $this->getHooks()->add( $this->add( $hook ) );
    }

    public function detach( \SplObserver $hook ) {
        if ( ! $hook instanceof HookInterface ) {
            throw new \InvalidArgumentException;
        }
        return $this->getHooks()->remove( $this->remove( $hook ) );
    }

    public function add( HookInterface $hook ) {
        $cb = $this->isFilter() ? '\add_filter' : '\add_action';
        call_user_func_array( $cb, $this->getHookArgs( $hook ) );
        return $hook;
    }

    public function remove( HookInterface $hook ) {
        $cb = $this->isFilter() ? '\remove_filter' : '\remove_action';
        call_user_func_array( $cb, $this->getHookArgs( $hook ) );
        return $hook;
    }

    public function notify() {
        $args = $this->getArgs();
        array_unshift( $args, $this->getId() );
        $cb = $this->isFilter() ? '\apply_filters' : '\do_action';
        $result = call_user_func_array( $cb, $this->getArgs() );
        if ( $this->isFilter() ) {
            return $result;
        }
    }

    public function detachAll() {
        foreach ( $this->getHooks() as $hook ) {
            $this->detach( $hook );
        }
    }

    public function removeAll() {
        foreach ( $this->getHooks() as $hook ) {
            $this->remove( $hook );
        }
    }

    public function restoreAll() {
        foreach ( $this->getHooks() as $hook ) {
            if ( $hook instanceof HookInterface ) {
                $this->add( $hook );
            }
        }
    }

    public function setId( $id ) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function setHooks( BucketInterface $bucket ) {
        $this->hooks = $bucket;
    }

    public function getHooks() {
        return $this->hooks;
    }

    public function getHook( $id ) {
        return $this->getHooks()->get( $id );
    }

    public function setArgs( Array $args ) {
        $this->args = $args;
    }

    public function getArgs() {
        return $this->args;
    }

    public function isFilter( $set = NULL ) {
        if ( $set === TRUE ) $this->filter = $set;
        return $this->filter;
    }

    public function getContext( $index = NULL ) {
        if ( is_null( $index ) ) {
            return $this->context->getArrayCopy();
        } elseif ( $this->context->offsetExists( $index ) ) {
            return $this->context[$index];
        }
    }

    public function setContext( $index = NULL, $value = NULL ) {
        if ( ! is_null( $index ) && ( ! is_string( $index ) || empty( $index ) ) ) {
            throw new \InvalidArgumentException;
        } elseif ( is_null( $index ) && ! is_null( $value ) ) {
            throw new \InvalidArgumentException;
        }
        if ( is_null( $index ) ) {
            $this->context = new \ArrayObject;
        } else {
            $this->context[$index] = $value;
        }
    }

    protected function getHookArgs( HookInterface $hook ) {
        return [
            $this->getId(),
            [ $hook, 'proxy' ],
            $hook->get( 'priority' ),
            $hook->get( 'args_num' )
        ];
    }

    protected function reset() {
        $this->args = [ ];
    }

}