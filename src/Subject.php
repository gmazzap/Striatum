<?php namespace Brain\Striatum;

class Subject implements SubjectInterface, \SplSubject {

    use Fullclonable,
        Contextable,
        Idable;

    protected $id;

    protected $hooks;

    protected $context;

    public function __construct( BucketInterface $bucket ) {
        $this->hooks = $bucket;
        $this->context = new \ArrayObject;
    }

    public function __get( $name ) {
        return $this->getInfo( $name );
    }

    public function attach( \SplObserver $hook ) {
        if ( ! $hook instanceof HookInterface ) {
            throw new \InvalidArgumentException;
        }
        if ( ! $hook->check() ) {
            throw new \DomainException;
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
        $args = array_values( (array) func_get_arg( 0 ) );
        if ( ! isset( $args[0] ) || $args[0] !== $this->getId() ) {
            array_unshift( $args, $this->getId() );
        }
        $cb = $this->isFilter() ? '\apply_filters' : '\do_action';
        $result = call_user_func_array( $cb, $args );
        if ( $this->isFilter() ) {
            return $result;
        }
    }

    public function detachAll() {
        foreach ( $this->getHooks()->getArrayCopy() as $hook ) {
            $this->detach( $hook );
        }
    }

    public function removeAll() {
        foreach ( $this->getHooks()->getArrayCopy() as $hook ) {
            if ( $hook instanceof HookInterface ) {
                $this->remove( $hook );
            }
        }
    }

    public function restoreAll() {
        foreach ( $this->getHooks()->getArrayCopy() as $hook ) {
            if ( $hook instanceof HookInterface ) {
                $this->add( $hook );
            }
        }
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

    public function isFilter( $set = NULL ) {
        if ( $set === TRUE ) $this->setInfo( 'is_filter', TRUE );
        return (bool) $this->getInfo( 'is_filter' );
    }

    public function getInfo( $info = NULL ) {
        return $this->getContext( 'context', $info );
    }

    public function setInfo( $info = NULL, $value = NULL ) {
        return $this->setContext( 'context', $info, $value );
    }

    public function getHooksArray() {
        return $this->getHooks()->getArrayCopy();
    }

    protected function getHookArgs( HookInterface $hook ) {
        return [
            $this->getId(),
            [ $hook, 'proxy' ],
            $hook->get( 'priority' ),
            $hook->get( 'args_num' )
        ];
    }

}