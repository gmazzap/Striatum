<?php namespace Brain\Striatum;

use Brain\Striatum\HookInterface;
use Brain\Striatum\SubjectsManager as Manager;

/**
 * Striatum API Class.
 *
 * ##############################################################################################
 * Striatum package allows an object-oriented way to handle WordPress hooks.
 * ##############################################################################################
 *
 * It uses an implementation of Observer pattern, where every hook is associated to a Subject
 * instance, that notify all the Observer instances attached: so where WP core plugin API uses
 * callbacks and global variables, this package uses objects.
 * Every Observer instace has an ID: using it is always possible identify and edit/remove an hook,
 * even if the associated callback is an object method or even a closure.
 *
 * This implementation act as wrapper for WordPress plugin API functions:
 * add_action and add_filter are used internally to add hooks and the *notify* method of Subject
 * classes uses do_action and apply_filters to perform actions.
 * remove_action and remove_filter are used to remove hooks.
 *
 * This class *glues* all the classes of Striatum package and ease the access to package features
 * giving methods built using same *flavor* of core functions, and gives consistency on returned
 * objects: on error all methods return a WP_Error instance: every exception thrown by package
 * classes is converted to a WP_Error.
 *
 * ##############################################################################################
 * Please note that ALL the methods in this class and in other package classes have effect on
 * observers / callbacks added via the package, callback added via core add_action / add_filter
 * can't be retrieved, edited or deleted using method in this class or in other package classes.
 * ##############################################################################################
 *
 * @package Brain\Striatum
 * @version 0.1
 */
class API {

    private $manager;

    private $hooks;

    private $hook;

    /**
     * Constructor.
     *
     * @param \Brain\Striatum\SubjectsManager $manager  Class that manages subject operations
     * @param \ArrayObject $hooks                       Container for added subjects
     * @param \Brain\Striatum\HookInterface $hook       Hook object instance used as prototype
     * @since 0.1
     */
    public function __construct( Manager $manager, \ArrayObject $hooks, HookInterface $hook ) {
        $this->manager = $manager;
        $this->hooks = $hooks;
        $this->hook = $hook;
    }

    /**
     * Given an action or filter hook (e.g. 'init' or 'the_title') returns the hook object attached.
     * Is possible to return the subject instance used for the hook instead of hooks array.
     *
     * @param string $hook          Action or filter to get the attached hooks for
     * @param boolean $get_subject  If true return the hook subject instead of hooks objects array
     * @return \Brain\Striatum\SubjectsManager|array|\WP_Error
     * @since 0.1
     */
    public function getHooks( $hook, $get_subject = FALSE ) {
        if ( ! is_string( $hook ) || empty( $hook ) ) {
            return new \WP_Error( 'hooks-bad-id' );
        }
        if ( ! isset( $this->hooks[$hook] ) || ! $this->hooks[$hook] instanceof SubjectInterface ) {
            return new \WP_Error( 'hooks-bad-subject' );
        }
        try {
            return $get_subject ? $this->hooks[$hook] : $this->hooks[$hook]->getHooksArray();
        } catch ( Exception $exc ) {
            return $this->errorFromException( $exc );
        }
    }

    /**
     * Given an action or filter hook (e.g. 'init' or 'the_title') and an observer id return the
     * observer object.
     *
     * @param string|\Brain\Striatum\SubjectInterface $hook Hook to get
     * @param string $id                                    Observer id
     * @return \Brain\Striatum\HookInterface|\WP_Error
     * @since 0.1
     */
    public function getHook( $hook, $id ) {
        $hooks = $hook instanceof SubjectInterface ? $hook : $this->getHooks( $hook, TRUE );
        if ( is_wp_error( $hooks ) ) {
            return $hooks;
        }
        try {
            return $hooks->getHook( $id );
        } catch ( Exception $exc ) {
            return $this->errorFromException( $exc );
        }
    }

    /**
     * Add an observer to an hook. This is the homologous of core add_action and add_filter.
     * the two types of hook are differentiated via $is_filter param.
     * This method is designed differently from core functions, but the two methods that make use it,
     * addFilter and addAction accepts same arguments of core ones.
     * Only additional arguments is 'times', that makes the callaback runs a given number of times.
     * Using this method is possible to add same callback to more than one hook.
     * Return the array of observer objects added or the single instance when only one is added.
     *
     *
     * @param string $id            Observer id
     * @param array $args           Observer args: callback, priority, accepted args and allowed times.
     * @param string|array $hook    Action or filter hook, e.g. 'init' or 'the_title'
     *                              Is possible to pass an array or a comma separed list of hook.
     * @param boolean $is_filter    If true add a filter, otherwise an action
     * @return array|\Brain\Striatum\HookInterface|\WP_Error
     * @see \Brain\Striatum\API::addAction()
     * @see \Brain\Striatum\API::addFilter()
     * @since 0.1
     */
    public function addHook( $id, $args, $hook = '', $is_filter = FALSE ) {
        if ( ( ! is_string( $hook ) && ! is_array( $hook ) ) || empty( $hook ) ) {
            return new \WP_Error( 'hooks-bad-hook-id' );
        }
        if ( ! is_string( $id ) || empty( $id ) ) {
            return new \WP_Error( 'hooks-bad-hook' );
        }
        try {
            $singular = is_string( $hook ) && ( substr_count( $hook, ',' ) === 0 );
            $subjects = $this->manager->addSubjects( $hook, $is_filter );
            $hookObject = clone $this->hook;
            $hookObject->prepare( $args );
            $added = [ ];
            foreach ( $subjects as $subject ) {
                $added[] = $this->addSubjectHook( $subject, $hookObject, $hook, $id, $args );
            }
            return $singular ? array_shift( $added ) : $added;
        } catch ( Exception $exc ) {
            return $this->errorFromException( $exc );
        }
    }

    /**
     * Modify an observer after it is added. This task is not not available in core plugin API.
     * Every aspect of the observer can be changed.
     * If the given observer id does not exists the method act as addHook, adding a new hook.
     *
     * @param string $hook
     * @param string|\Brain\Striatum\HookInterface $observer Action or filter hook, e.g. 'init'
     * @param type $args                                    New observer params
     * @param type $new_as_filter                           When the observer does not exists, a new one
     *                                                      is created and if this param is true it's created
     *                                                      as filter
     * @return \Brain\Striatum\HookInterface|\WP_Error
     * @since 0.1
     */
    public function updateHook( $hook, $observer, $args = [ ], $new_as_filter = FALSE ) {
        if ( ! is_string( $hook ) || empty( $hook ) ) {
            return new \WP_Error( 'hooks-bad-hook' );
        }
        if ( ( ! is_string( $observer ) && ! $observer instanceof HookInterface ) ) {
            return new \WP_Error( 'hooks-bad-hook-id' );
        }
        $hookObject = $observer instanceof HookInterface ? $observer : NULL;
        if ( is_null( $hookObject ) ) {
            $hookObject = $this->getHook( $hook, $observer );
            if ( is_wp_error( $hookObject ) ) {
                return $hookObject;
            }
            try {
                return $this->addHook( $observer, $args, $hook, $new_as_filter );
            } catch ( Exception $exc ) {
                return $this->errorFromException( $exc );
            }
        }
        if ( $hookObject instanceof HookInterface ) {
            try {
                return $hookObject->prepare( $args );
            } catch ( Exception $exc ) {
                return $this->errorFromException( $exc );
            }
        }
    }

    /**
     * Add an observer to a filter hook. This is the homologous of core add_filter and takes
     * almost same arguments.
     * There are 2 different arguments, the first and the last.
     * The first is the observer id, that can be used to retrieve/update/remove the observer.
     * The last is 'times' that makes the callaback run a given number of times, so creating
     * sort of self-removing observer.
     * Unlike core add_filter is possible to add the same observer to different hooks, passing an
     * array or a comma separed list of filters.
     *
     * @param string $id            Observer id
     * @param string|array $hook    Action or filter hook, e.g. 'init' or 'the_title'
     *                              Is possible to pass an array or a comma separed list of hook.
     * @param callable $callback    The callback to associate to observer
     * @param int $priority         Observer priority. Default 10.
     * @param int $args_num         Accepted arguments number. Default 1.
     * @param int $times            Make observer run a given number of times. Default 0 (no limit).
     * @return array|\Brain\Striatum\HookInterface|\WP_Error
     * @uses \Brain\Striatum\API::addHook()
     * @see \add_filter()
     * @since 0.1
     */
    public function addFilter( $id, $hook, $callback, $priority = 10, $args_num = 1, $times = 0 ) {
        $args = compact( 'callback', 'priority', 'args_num', 'times' );
        return $this->addHook( $id, $args, $hook, TRUE );
    }

    /**
     * Add an observer to an action hook. This is the homologous of core add_action and takes
     * almost same arguments.
     * There are 2 different arguments, the first and the last.
     * The first is the observer id, that can be used to retrieve/update/remove the observer.
     * The last is 'times' that makes the callaback run a given number of times, so creating
     * sort of self-removing observer.
     * Unlike core add_action is possible to add the same observer to different hooks, passing an
     * array or a comma separed list of action.
     *
     * @param string $id            Observer id
     * @param string|array $hook    Action or filter hook, e.g. 'init' or 'the_title'
     *                              Is possible to pass an array or a comma separed list of hook.
     * @param callable $callback    The callback to associate to observer
     * @param int $priority         Observer priority. Default 10.
     * @param int $args_num         Accepted arguments number. Default 1.
     * @param int $times            Make observer run a given number of times. Default 0 (no limit).
     * @return array|\Brain\Striatum\HookInterface|\WP_Error
     * @uses \Brain\Striatum\API::addHook()
     * @see \add_action()
     * @since 0.1
     */
    public function addAction( $id, $hook, $callback, $priority = 10, $args_num = 1, $times = 0 ) {
        $args = compact( 'callback', 'priority', 'args_num', 'times' );
        return $this->addHook( $id, $args, $hook, FALSE );
    }

    /**
     * Remove an observer form an hook. Is the homologous for core remove_action and remove_filter.
     * However instead of passing the callback to remove (that is hard when is an object method and
     * harder when is a closure) the methods accepts the observer id, so is very easy remove any
     * type of callback.
     *
     * @param string $hook  Action or filter hook, e.g. 'init' or 'the_title'
     * @param string $id    Observer id. Is first param passed to addAction, addFilter or addHook
     * @return void|\WP_Error
     * @since 0.1
     */
    public function removeHook( $hook, $id ) {
        try {
            $hookObject = NULL;
            $subject = $this->manager->getSubject( $hook );
            if ( $subject instanceof SubjectInterface ) {
                $hookObject = $subject->getHook( $id );
            }
            if ( $hookObject instanceof HookInterface ) {
                $subject->detach( $hookObject );
            }
        } catch ( Exception $exc ) {
            return $this->errorFromException( $exc );
        }
    }

    /**
     * Remove all observers form an hook. Is similar to core remove_all_actions().
     * However it accepts also an array or a comma separed lists of actions and/or filters to remove
     * all observers from different hooks.
     *
     * @param string|array $hooks   Action or filter hook, e.g. 'init' or 'the_title'
     *                              Is possible to pass an array or a comma separed list of hook.
     * @return void|\WP_Error
     * @since 0.1
     */
    public function removeHooks( $hooks ) {
        try {
            $this->manager->removeSubjects( $hooks );
        } catch ( Exception $exc ) {
            return $this->errorFromException( $exc );
        }
    }

    /**
     * Freezing an hook means that all the observer and their params are saved, however they are
     * temporarly suspended, so do nothing until they are not unfreezed.
     * Is possible to freeze more than one hook with a single call, using an array or a comma
     * separed list of hook.
     *
     * @param string|array $hooks   Action or filter hook, e.g. 'init' or 'the_title'
     *                              Is possible to pass an array or a comma separed list of hook.
     * @return void|\WP_Error
     * @see \Brain\Striatum\API::unfreezeHooks()
     * @since 0.1
     */
    public function freezeHooks( $hooks ) {
        try {
            $this->manager->freezeSubjects( $hooks );
        } catch ( Exception $exc ) {
            return $this->errorFromException( $exc );
        }
    }

    /**
     * Unfreeze one or more hooks previously frozen using freezeHooks()
     *
     * @param string|array $hooks   Action or filter hook, e.g. 'init' or 'the_title'
     *                              Is possible to pass an array or a comma separed list of hook.
     * @return void|\WP_Error
     * @see \Brain\Striatum\API::freezeHooks()
     * @since 0.1
     */
    public function unfreezeHooks( $hooks ) {
        try {
            $this->manager->unfreezeSubjects( $hooks );
        } catch ( Exception $exc ) {
            return $this->errorFromException( $exc );
        }
    }

    /**
     * Trigger an hook. Homologous for core do_action and apply_filters.
     * If the hook is a filter than the method return whatever returned by all the attached
     * observer callabacks.
     *
     * @param string $hook  Action or filter hook, e.g. 'init' or 'the_title'
     * @return mixed        When the hook is a filter method returns the filters results.
     *                      Nothing is returned for actions.
     * @since 0.1
     */
    public function trigger( $hook ) {
        $hooks = $this->getHooks( $hook, TRUE );
        if ( is_wp_error( $hooks ) ) {
            return $hooks;
        }
        $all_args = func_get_args();
        $args = func_get_arg( 1 ) ? array_slice( $all_args, 1 ) : [ ];
        try {
            $result = $hooks->notify( $args );
            if ( $hooks->isFilter() ) return $result;
        } catch ( Exception $exc ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                return $this->errorFromException( $exc );
            }
            if ( $hooks->isFilter() ) return $args[0];
        }
    }

    /**
     * Homologous for core apply_filters, run trigger() method after having prepended the actual
     * value to args array.
     *
     * @param string $hook  Action or filter hook, e.g. 'init' or 'the_title'
     * @param mixed $actual Actual value to be filtered
     * @return mixed
     * @uses \Brain\Striatum\API::trigger()
     * @see \apply_filters()
     * @since 0.1
     */
    public function filter( $hook, $actual = NULL ) {
        $all_args = func_get_args();
        array_unshift( $all_args, $actual );
        array_unshift( $all_args, $hook );
        return call_user_func_array( [ $this, 'trigger' ], $all_args );
    }

    /**
     * Given an hook (or a subject object) and an observer id, check if the observer is added to
     * the hook. If true, returns true unless the $return_hook param is true, in that case returns
     * the hook object.
     *
     * @param string|\Brain\Striatum\SubjectInterface $hook Action or filter hook, e.g. 'init'
     *                                                      or a Subject object
     * @param string $id                                    Observer id
     * @param boolean $return_hook                          If true, returns hook object instead of
     *                                                      TRUE. No effect when check is false.
     * @return boolean|\Brain\Striatum\HookInterface|\WP_Error
     * @since 0.1
     */
    public function hookHas( $hook, $id, $return_hook = FALSE ) {
        $hooks = $hook instanceof SubjectInterface ? $hook : $this->getHooks( $hook );
        if ( is_wp_error( $hook ) ) {
            return $hook;
        }
        if ( ! $hooks instanceof SubjectInterface ) {
            return FALSE;
        }
        try {
            $hook = $hooks->getHook( $id );
            if ( $hook instanceof HookInterface ) {
                return $return_hook ? $hook : TRUE;
            }
            return FALSE;
        } catch ( Exception $exc ) {
            return $this->errorFromException( $exc );
        }
    }

    /**
     * Given an hook (or a subject object) and an observer id returns true if the observer is added
     * to the hook and the hook is an action. False otherwise.
     *
     * @param string|\Brain\Striatum\SubjectInterface $hook Action or filter hook or Subject object
     * @param string $id                                    Observer id
     * @return boolean|\WP_Error
     * @uses \Brain\Striatum\API::hookHas()
     * @since 0.1
     */
    public function actionHas( $hook, $id ) {
        $hook = $this->hookHas( $hook, $id, TRUE );
        if ( is_wp_error( $hook ) ) {
            return $hook;
        }
        return $hook instanceof HookInterface && ! $hook->isFilter();
    }

    /**
     * Given an hook (or a subject object) and an observer id returns true if the observer is added
     * to the hook and the hook is a filter. False otherwise.
     *
     * @param string|\Brain\Striatum\SubjectInterface $hook Action / filter hook or Subject object
     * @param string $id                                    Observer id
     * @return boolean|\WP_Error
     * @uses \Brain\Striatum\API::hookHas()
     * @since 0.1
     */
    public function filterHas( $hook, $id ) {
        $hook = $this->hookHas( $hook, $id, TRUE );
        if ( is_wp_error( $hook ) ) {
            return $hook;
        }
        return $hook instanceof HookInterface && $hook->isFilter();
    }

    /**
     * Similar to core doing_action() (introduced with WP 3.9) given an hook and an observer id
     * returns true if the observer is being performed, even inside nested callbacks or nested hooks
     *
     * @param string $hook Action or filter hook, e.g. 'init' or 'the_title'
     * @param string $id
     * @return boolean
     * @since 0.1
     */
    public function callbackDoing( $hook, $id ) {
        $hooks = $this->getHooks( $hook );
        if ( is_wp_error( $hooks ) ) {
            return $hooks;
        }
        return $hooks instanceof SubjectInterface && in_array( $id, (array) $hooks->calling );
    }

    /**
     * Similar to core did_action() given an hook and an observer id returns true if the observer
     * callback was performed. Unlike did_action() this method works also for filters and returns
     * false when the callback is being performed.
     *
     * @param string $hook Action or filter hook, e.g. 'init' or 'the_title'
     * @param string $id
     * @return boolean
     * @since 0.1
     */
    public function callbackDone( $hook, $id ) {
        $hooks = $this->getHooks( $hook );
        if ( is_wp_error( $hooks ) ) {
            return $hooks;
        }
        return $hooks instanceof SubjectInterface ? in_array( $id, (array) $hooks->called ) : FALSE;
    }

    /**
     * Given an hook, return the last callback id (if any) ran for the hook.
     *
     * @param string $hook  Action or filter hook, e.g. 'init' or 'the_title'
     * @return void|string  Last observer id or null
     * @since 0.1
     */
    public function callbackLast( $hook ) {
        $hooks = $this->getHooks( $hook );
        if ( is_wp_error( $hooks ) ) {
            return $hooks;
        }
        return $hooks instanceof SubjectInterface ? $hooks->last_callback : NULL;
    }

    /**
     * Given an hook and an observer id, return the last arguments array passed to the callback.
     *
     * @param string $hook Action or filter hook, e.g. 'init' or 'the_title'
     * @param string $id
     * @return array|void
     * @since 0.1
     */
    public function callbackLastArgs( $hook, $id ) {
        $hook = $this->getHook( $hook, $id );
        if ( is_wp_error( $hook ) ) {
            return $hook;
        }
        return $hook instanceof HookInterface ? $hook->last_args : NULL;
    }

    /**
     * Given an hook, if is currently running, return the priority being performed.
     * Returns false if no observer are added to hook or it is not currently performed.
     *
     * @param string $hook Action or filter hook, e.g. 'init' or 'the_title'
     * @param string $id
     * @return int|boolean  Current priority or false
     * @since 0.1
     */
    public function doingPriority( $hook ) {
        $hooks = $this->getHooks( $hook );
        if ( is_wp_error( $hooks ) ) {
            return $hooks;
        }
        $is = $hooks instanceof SubjectInterface ? $hooks->priority_now : FALSE;
        return is_int( $is ) ? $is : FALSE;
    }

    /**
     * Convert an exception to a WP_Error.
     *
     * @return \WP_Error
     * @access private
     */
    private function errorFromException( Exception $exc ) {
        $name = get_class( $exc );
        $name .= $exc->getCode() ? '-' . $exc->getCode() : '';
        $msg = $exc->getMessage() ? : '';
        return new \WP_Error( 'hooks-exception-' . $name, $msg );
    }

    /**
     * Internally used by add_hook()
     *
     * @access private
     * @see \Brain\Striatum\API::addHook()
     */
    private function addSubjectHook( SubjectInterface $s, HookInterface $h, $hook, $id, $args ) {
        $exists = $this->getHook( $hook, $id );
        if ( $exists instanceof HookInterface ) {
            return $this->updateHook( $hook, $exists, $args );
        } else {
            $h->setId( $id );
            $h->setSubject( $s );
            return $s->attach( $h );
        }
    }

}