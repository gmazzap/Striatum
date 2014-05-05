<?php namespace Brain\Striatum;

class Hook implements HookInterface, \SplObserver {

    use Fullclonable,
        Contextable,
        Idable;

    protected $id;

    private $times = 0;

    private $context;

    function __construct() {
        $this->context = new \ArrayObject();
    }

    public function __get( $name ) {
        return $this->get( $name );
    }

    public function __call( $name, $arguments ) {
        if ( strpos( $name, 'get' ) === 0 && empty( $arguments ) ) {
            $name = strtolower( substr( $name, 3 ) );
            return $this->get( $name );
        } elseif ( strpos( $name, 'run' ) === 0 ) {
            $times = strtolower( substr( $name, 3 ) );
            $matches = [ ];
            if ( in_array( $times, [ 'once', 'twice' ], TRUE ) ) {
                $n = $times === 'twice' ? 2 : 1;
                return $this->runNTimes( $n );
            } elseif ( preg_match( '/^run([0-9]+)+times$/i', $name, $matches ) ) {
                return $this->runNTimes( $matches[1] );
            }
        }
    }

    public function get( $index = NULL ) {
        return $this->getContext( 'context', $index );
    }

    public function set( $index = NULL, $value = NULL ) {
        return $this->setContext( 'context', $index, $value );
    }

    public function setSubject( SubjectInterface $subject ) {
        return $this->set( 'subject', $subject );
    }

    public function getSubject() {
        return $this->get( 'subject' );
    }

    public function prepare( $args ) {
        if ( ! is_array( $args ) && ! is_string( $args ) ) {
            throw new \InvalidArgumentException;
        }
        $def = [ 'priority' => 10, 'args_num' => 1, 'times' => 0, 'callback' => NULL ];
        $args = wp_parse_args( $args, $def );
        if ( ! is_numeric( $args['priority'] ) ) {
            $args['priority'] = 10;
        }
        if ( ! is_numeric( $args['args_num'] ) ) {
            $args['args_num'] = 1;
        }
        if ( ! is_numeric( $args['times'] ) ) {
            $args['args_num'] = 0;
        }
        if ( ! is_callable( $args['callback'] ) ) {
            $args['callback'] = NULL;
        }
        $this->set( 'priority', (int) $args['priority'] );
        $this->set( 'args_num', (int) $args['args_num'] );
        $this->set( 'times', (int) $args['times'] );
        $this->set( 'callback', $args['callback'] );
        return $this;
    }

    public function proxy() {
        if ( ! $this->check() ) {
            return func_num_args() > 0 ? func_get_arg( 0 ) : NULL;
        }
        $args = array_slice( func_get_args(), 0, $this->get( 'args_num' ) );
        $this->setLastArgs( $args );
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
        return $this->set( 'last_args', $args );
    }

    public function getLastArgs() {
        return $this->get( 'last_args' );
    }

    public function runNTimes( $n = 1 ) {
        if ( ! is_numeric( $n ) || (int) $n < 0 ) {
            throw new \InvalidArgumentException;
        }
        return $this->set( 'times', (int) $n );
    }

    function check() {
        $sub = $this->getSubject() instanceof SubjectInterface;
        $id = $this->getId() ? : FALSE;
        $callback = $this->get( 'callback' );
        return $sub && is_string( $id ) && is_callable( $callback );
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
        $subject->setInfo( 'priority_now', $this->get( 'priority' ) );
        $subject->setInfo( 'callback_now', $id );
        $calling = $subject->getInfo( 'calling' ) ? : [ ];
        $subject->setInfo( 'calling', array_merge( $calling, [ $id ] ) );
        return $this;
    }

    function after( SubjectInterface $subject ) {
        $id = $this->getId();
        if ( empty( $id ) || ! is_string( $id ) ) {
            throw new \InvalidArgumentException;
        }
        $calling = $subject->getInfo( 'calling' ) ? : [ ];
        $called = $subject->getInfo( 'called' ) ? : [ ];
        $subject->setInfo( 'calling', array_diff( $calling, [ $id ] ) );
        $subject->setInfo( 'priority_now', NULL );
        $subject->setInfo( 'callback_now', NULL );
        $subject->setInfo( 'called', array_merge( $called, [ $id ] ) );
        $subject->setInfo( 'last_callback', $id );
        $this->times ++;
        $times = $this->get( 'times' );
        if ( ( $times > 0 ) && $this->times >= $times ) {
            return $subject->detach( $this );
        }
        return $this;
    }

}