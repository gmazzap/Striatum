<?php

namespace Brain\Striatum\Tests;

class HooksMock {

    static $hooks = [ 'actions' => [ ], 'filters' => [ ] ];

    static $hooks_done = [ 'actions' => [ ], 'filters' => [ ] ];

    /**
     * Reset static arrays
     */
    public static function tearDown() {
        static::$hooks = [ 'actions' => [ ], 'filters' => [ ] ];
        static::$hooks_done = [ 'actions' => [ ], 'filters' => [ ] ];
    }

    /**
     * Emulate add_action() and add_filter() depending on $type param.
     *
     * @param string $type Type of the hook, 'action' or 'filter'
     * @param array $args Arguments passed to add_action() or add_filter()
     * @throws Brain\Striatum\Tests\HookException
     * @return void
     */
    public static function addHook( $type = '', Array $args = [ ] ) {
        if ( ! in_array( $type, [ 'action', 'filter' ], TRUE ) ) $type = 'action';
        $target = $type === 'filter' ? 'filters' : 'actions';
        $hook = array_shift( $args );
        if ( empty( $hook ) || ! is_string( $hook ) ) {
            $msg = ' Error on adding ' . $type . ': invalid hook';
            throw new HookException( $msg );
        }
        $cb = array_shift( $args );
        if ( ! is_callable( $cb ) ) {
            $msg = ' Error on adding ' . $type . ': given callback for the hook ' . $hook
                . ' is not a valid callback.';
            throw new HookException( $msg );
        }
        $priority = array_shift( $args ) ? : 10;
        $num_args = array_shift( $args ) ? : 1;
        if ( ! isset( HooksMock::$hooks[$target][$hook] ) ) {
            static::$hooks[$target][$hook] = [ ];
        }
        if ( ! isset( static::$hooks[$target][$hook][$priority] ) ) {
            static::$hooks[$target][$hook][$priority] = [ ];
        }
        $id = static::callbackUniqueId( $cb );
        static::$hooks[$target][$hook][$priority][$id] = [ 'cb' => $cb, 'num_args' => $num_args ];
    }

    /**
     * Emulate remove_action() or remove_filter() depending on $type param.
     *
     * @param string $type Type of the hook, 'action' or 'filter'
     * @param array $args Arguments passed to remove_action() or remove_filter()
     * @throws Brain\Striatum\Tests\HookException
     * @return void
     */
    public static function removeHook( $type = '', Array $args = [ ] ) {
        if ( ! in_array( $type, [ 'action', 'filter' ], TRUE ) ) $type = 'action';
        $target = $type === 'filter' ? 'filters' : 'actions';
        $hook = array_shift( $args );
        if ( empty( $hook ) || ! is_string( $hook ) ) {
            $msg = ' Error on removing ' . $type . ': invalid hook';
            throw new HookException( $msg );
        }
        $cb = array_shift( $args );
        if ( ! is_callable( $cb ) ) {
            $msg = ' Error on removing ' . $type . ': given callback for the hook ' . $hook
                . ' is not a valid callback.';
            throw new HookException( $msg );
        }
        $id = static::callbackUniqueId( $cb );
        $priority = array_shift( $args ) ? : 10;
        $num_args = ! empty( $args ) ? array_shift( $args ) : -1;
        if ( ! array_key_exists( $hook, HooksMock::$hooks[$target] ) ) return;
        if ( ! array_key_exists( $priority, HooksMock::$hooks[$target][$hook] ) ) return;
        if ( array_key_exists( $id, HooksMock::$hooks[$target][$hook][$priority] ) ) {
            $data = HooksMock::$hooks[$target][$hook][$priority][$id];
            $data['num_args'] = isset( $data['num_args'] ) ? (int) $data['num_args'] : 1;
            if ( (int) $num_args > 0 && $data['num_args'] !== (int) $num_args ) return;
            unset( HooksMock::$hooks[$target][$hook][$priority][$id] );
        }
    }

    /**
     * Emulate do_action() or apply_filters() depending on $type param.
     *
     * @param string $type Type of the hook, 'action' or 'filter'
     * @param array $args Arguments passed to do_action() or apply_filters()
     * @throws Brain\Striatum\Tests\HookException
     * @return array 3 items array, 1st is the type, 2nd the hook fired, 3rd the arguments
     */
    public static function fireHook( $type = '', $args = [ ] ) {
        if ( ! in_array( $type, [ 'action', 'filter' ], TRUE ) ) $type = 'action';
        $target = $type === 'filter' ? 'filters' : 'actions';
        if ( empty( $args ) || ! is_array( $args ) ) {
            $msg = ' Error on adding ' . $type . ': invalid arguments.';
            throw new HookException( $msg );
        }
        $args = array_values( $args );
        $hook = array_shift( $args );
        if ( empty( $hook ) || ! is_string( $hook ) ) {
            $msg = ' Error on adding ' . $type . ': invalid hook';
            throw new HookException( $msg );
        }
        if ( ! isset( static::$hooks_done[$type][$hook] ) ) {
            static::$hooks_done[$target][$hook] = [ ];
        }
        static::$hooks_done[$target][$hook][] = $args;
        return [ $type, $hook, $args ];
    }

    /**
     * Check if an action hook is added. Optionally check a specific callback and and priority.
     *
     * @param string $hook Hook to check
     * @param callable $callback Callback to check
     * @param int $priority Priority to check
     * @return boolean
     * @throws HookException
     * @uses Brain\Striatum\Tests\HooksMock::hasHook()
     */
    public static function hasAction( $hook = '', $callback = NULL, $priority = NULL ) {
        return static::hasHook( 'action', $hook, $callback, $priority );
    }

    /**
     * Check if an filter hook is added. Optionally check a specific callback and and priority.
     *
     * @param string $hook Hook to check
     * @param callable $callback Callback to check
     * @param int $priority Priority to check
     * @return boolean
     * @throws HookException
     * @uses Brain\Striatum\Tests\HooksMock::hasHook()
     */
    public static function hasFilter( $hook = '', $callback = NULL, $priority = NULL ) {
        return static::hasHook( 'filter', $hook, $callback, $priority );
    }

    /**
     * Check if an action is added and throws a exceptions otherwise.
     *
     * @param string $hook Action hook to check
     * @param callable $cb Callback to check
     * @param int $pri Priority to check
     * @param int $n_args Number of accepted arguments to check
     * @uses Brain\Striatum\Tests\HooksMock::hookAddedTest()
     */
    public static function assertActionAdded( $hook = '', $cb = NULL, $pri = NULL, $n_args = NULL ) {
        static::assertHookAdded( 'action', $hook, $cb, $pri, $n_args );
    }

    /**
     * Check if a filter is added and throws an exceptions otherwise.
     *
     * @param string $hook Filter hook to check
     * @param callable $cb Callback to check
     * @param int $pri Priority to check
     * @param int $n_args Number of accepted arguments to check
     * @uses Brain\Striatum\Tests\HooksMock::hookAddedTest()
     */
    public static function assertFilterAdded( $hook = '', $cb = NULL, $pri = NULL, $n_args = NULL ) {
        static::assertHookAdded( 'filter', $hook, $cb, $pri, $n_args );
    }

    /**
     * Check if an action was fired. Optionally checks if given callback was fired on given action.
     * Throws an exception if assertion is wrong.
     *
     * @param string $hook Action hook to check
     * @param callable $cb Callback to check
     * @uses Brain\Striatum\Tests\HooksMock::hookFiredTest()
     */
    public static function assertActionFired( $hook = NULL, $cb = NULL ) {
        static::assertHookFired( 'action', $hook, $cb );
    }

    /**
     * Check if a filter was fired. Optionally checks if given callback was fired on given filter.
     * Throws an exception if assertion is wrong.
     *
     * @param string $hook Filter hook to check
     * @param callable $cb Callback to check
     * @uses Brain\Striatum\Tests\HooksMock::hookFiredTest()
     */
    public static function assertFilterFired( $hook = NULL, $cb = NULL ) {
        static::assertHookFired( 'filter', $hook, $cb );
    }

    /**
     * Equivalent to _wp_filter_build_unique_id() generate an unique id for a given callback
     *
     * @param callable $callback Callback to generate the unique id from
     * @throws Brain\Striatum\Tests\HookException
     */
    public static function callbackUniqueId( $callback = NULL ) {
        if ( ! is_callable( $callback ) ) {
            $msg = 'Use a valid callback with ' . __METHOD__ . '.';
            throw new HookException( $msg );
        }
        if ( is_string( $callback ) ) return $callback;
        if ( is_object( $callback ) ) {
            $callback = [ $callback, '' ];
        } else {
            $callback = (array) $callback;
        }
        if ( is_object( $callback[0] ) ) {
            return spl_object_hash( $callback[0] ) . $callback[1];
        } else if ( is_string( $callback[0] ) ) {
            return $callback[0] . '::' . $callback[1];
        }
    }

    /**
     * @param string $type Type of hook, 'action' or 'filter'
     * @param string $hook Hook to check
     * @param callable $cb Callback to check
     * @param int $pri Priority to check
     * @return boolean
     * @throws HookException
     */
    public static function hasHook( $type = '', $hook = '', $cb = NULL, $pri = NULL ) {
        if ( ! in_array( $type, [ 'action', 'filter' ], TRUE ) ) $type = 'action';
        $target = $type === 'filter' ? 'filters' : 'actions';
        if ( empty( $hook ) || ! is_string( $hook ) ) {
            $msg = ' Error on checking ' . $type . ': invalid hook';
            throw new HookException( $msg );
        }
        $id = "{$hook} {$type}";
        if ( ! is_null( $cb ) && ! is_callable( $cb ) ) {
            $msg = ' Error on checking ' . $id . ': the one given is not a valid callback.';
            throw new HookException( $msg );
        }
        if ( ! is_null( $pri ) && ( ! is_numeric( $pri ) || (int) $pri < 0 ) ) {
            $msg = ' Error on checking ' . $id . ': the one given is not a valid prioriry.';
            throw new HookException( $msg );
        }
        if ( ! array_key_exists( $hook, static::$hooks[$target] ) ) return FALSE;
        if ( is_null( $cb ) ) return TRUE;
        $hooks = static::$hooks[$target][$hook];
        $cbid = static::callbackUniqueId( $cb );
        if ( ! is_null( $pri ) ) {
            return array_key_exists( $pri, $hooks ) && array_key_exists( $cbid, $hooks[$pri] );
        } else {
            foreach ( $hooks as $_cbid => $cbdata ) {
                if ( $_cbid === $cbid && isset( $cbdata['cb'] ) ) return TRUE;
            }
        }
        return false;
    }

    /**
     * @param string $t Type of hook, 'action' or 'filter'
     * @param string $h Action hook to check
     * @param callable $cb Callback to check
     * @param int $p Priority to check
     * @param int $n Number of accepted arguments to check
     * @throws Brain\Striatum\Tests\HookException
     * @access protected
     */
    public static function assertHookAdded( $t = '', $h = '', $cb = NULL, $p = NULL, $n = NULL ) {
        if ( ! in_array( $t, [ 'action', 'filter' ], TRUE ) ) $t = 'action';
        $target = $t === 'filter' ? 'filters' : 'actions';
        if ( empty( $h ) || ! is_string( $h ) ) {
            $msg = __METHOD__ . ' needs a valid hook to check.';
            throw new HookException( $msg );
        }
        $id = "{$h} {$t}";
        if ( empty( $cb ) ) {
            $msg = 'Use a valid callback to check for ' . $id . '.';
            throw new HookException( $msg );
        }
        if ( ! array_key_exists( $h, static::$hooks[$target] ) ) {
            $msg = $h . 'is not a valid ' . $t . ' added.';
            throw new HookException( $msg );
        }
        $hooks = static::$hooks[$target][$h];
        if ( ! is_null( $p ) && ! is_numeric( $p ) ) {
            $msg = $p . 'Not numeric priority to check for ' . $id;
            throw new HookException( $msg );
        }
        if ( ! is_null( $n ) && ! is_numeric( $n ) ) {
            $msg = $n . 'Not numeric accepted args num to check for ' . $id;
            throw new HookException( $msg );
        }
        $priority = (int) $p ? : 10;
        $num_args = (int) $n ? : 1;
        if ( ! isset( $hooks[$priority] ) ) {
            $msg = 'Non valid priority ' . $priority . ' for ' . $id;
            if ( is_null( $p ) ) $msg = '. Be sure to pass exact priority to ' . __METHOD__;
            throw new HookException( $msg );
        }
        $cbid = static::callbackUniqueId( $cb );
        if ( ! array_key_exists( $cbid, (array) $hooks[$priority] ) ) {
            $msg = $n . 'Wrong callback for ' . $id . ' at priority ' . $priority;
            throw new HookException( $msg );
        }
        if ( is_null( $n ) ) return;
        $setted_num_args = isset( $hooks[$priority][$cbid]['num_args'] );
        if ( ! $setted_num_args || $num_args !== (int) $hooks[$priority][$cbid]['num_args'] ) {
            $msg = $num_args . ' is a wrong accepted args num for given callback on the ' . $id;
            throw new HookException( $msg );
        }
    }

    /**
     * @param string $type Type of hook, 'action' or 'filter'
     * @param string $hook Filter hook to check
     * @param callable $args Arguments to check
     * @throws Brain\Striatum\Tests\HookException
     * @access protected
     */
    public static function assertHookFired( $type = 'action', $hook = NULL, $args = NULL ) {
        if ( ! in_array( $type, [ 'action', 'filter' ], TRUE ) ) $type = 'action';
        $target = $type === 'filter' ? 'filters' : 'actions';
        if ( empty( $hook ) || ! is_string( $hook ) ) {
            $msg = __METHOD__ . ' needs a valid hook to check.';
            throw new HookException( $msg );
        }
        if ( empty( $hook ) || ! is_string( $hook ) ) {
            $msg = 'Invalid hook to check for ' . $type . 'Fired.';
            throw new HookException( $msg );
        }
        $id = "{$hook} {$type}";
        if ( ! array_key_exists( $hook, static::$hooks_done[$target] ) ) {
            $msg = $id . ' was not fired.';
            throw new HookException( $msg );
        }
        if ( is_null( $args ) ) return;
        if ( ! is_array( $args ) ) {
            $msg = 'Invalid arguments to check for ' . $id;
            throw new HookException( $msg );
        }
        $args = array_values( $args );
        if ( ! in_array( $args, static::$hooks_done[$target][$hook] ) ) {
            $msg = 'Arguments given were not fired check during ' . $id;
            throw new HookException( $msg );
        }
    }

}