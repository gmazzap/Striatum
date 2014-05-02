<?php

namespace Brain\Striatum;

class Hook implements HookInterface, \SplObserver {

    private $times = 0;

    private $storage;

    protected $last_args = [ ];

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
        $args = wp_parse_args( $args, $def );
        $args['priority'] = (int) $args['priority'];
        $args['args_num'] = (int) $args['args_num'];
        $args['times'] = (int) $args['times'];
        if ( ! is_callable( $args['callback'] ) ) $args['callback'] = NULL;
        $this->storage['args'] = $args;
        return $this;
    }

    public function proxy() {
        if ( ! $this->check() ) {
            return func_num_args() > 0 ? func_get_arg( 0 ) : NULL;
        }
        $this->setLastArgs( func_get_args() );
        return $this->update( $this->getSubject() );
    }

    public function update( \SplSubject $subject ) {
        if ( ! $subject instanceof SubjectInterface ) {
            throw new \InvalidArgumentException;
        }
        $hook = $this->before( $subject );
        $update = call_user_func_array( $hook->get( 'callback' ), $hook->getLastArgs() );
        $after = $hook->after( $subject );
        if ( $after instanceof HookInterface && $subject->isFilter() ) {
            return $update;
        }
    }

    public function setLastArgs( $args = [ ] ) {
        if ( ! is_array( $args ) ) {
            throw new \InvalidArgumentException;
        }
        $this->storage['last_args'] = $args;
        return $this;
    }

    public function getLastArgs() {
        return $this->storage['last_args'];
    }

    public function runNTimes( $n = 1 ) {
        if ( ! is_numeric( $n ) || (int) $n < 0 ) {
            throw new \InvalidArgumentException;
        }
        $this->set( 'times', (int) $n );
        return $this;
    }

    function check() {
        $sub = $this->getSubject() instanceof SubjectInterface;
        $id = $this->getId() ? : FALSE;
        return $sub && is_string( $id ) && is_callable( $this->get( 'callback' ) );
    }

    function before( SubjectInterface $subject ) {
        $id = $this->getId();
        if ( empty( $id ) || ! is_string( $id ) ) {
            throw new \InvalidArgumentException;
        }
        $times = $this->get( 'times' );
        if ( ( $times > 0 ) && $times >= $this->times ) {
            return $subject->detach( $this );
        }
        $subject->setContext( 'priority_now', $this->get( 'priority' ) );
        $subject->setContext( 'callback_now', $id );
        $calling = $subject->getContext( 'calling' ) ? : [ ];
        $subject->setContext( 'calling', array_merge( $calling, [ $id ] ) );
        return $this;
    }

    function after( SubjectInterface $subject ) {
        $id = $this->getId();
        if ( empty( $id ) || ! is_string( $id ) ) {
            throw new \InvalidArgumentException;
        }
        $calling = $subject->getContext( 'calling' ) ? : [ ];
        $called = $subject->getContext( 'called' ) ? : [ ];
        $subject->setContext( 'calling', array_diff( $calling, [ $id ] ) );
        $subject->setContext( 'priority_now', NULL );
        $subject->setContext( 'callback_now', NULL );
        $subject->setContext( 'called', array_merge( $called, [ $id ] ) );
        $subject->setContext( 'last_callback', $id );
        $this->times ++;
        $times = $this->get( 'times' );
        if ( ( $times > 0 ) && $this->times >= $times ) {
            return $subject->detach( $this );
        }
        return $this;
    }

}