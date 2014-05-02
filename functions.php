<?php

namespace Brain;

use \Brain\Striatum\SubjectInterface as Subject;
use \Brain\Striatum\HookInterface as Hook;

function getHooks( $hook, $get_subject = FALSE ) {
    if ( ! is_string( $hook ) || empty( $hook ) ) {
        return new \WP_Error( 'hooks-bad-id' );
    }
    $subjects = Container::instance()->get( 'striatum.hooks' );
    if ( ! isset( $subjects[$hook] ) || ! $subjects[$hook] instanceof Subject ) {
        return new \WP_Error( 'hooks-bad-subject' );
    }
    try {
        return $get_subject ? $subjects[$hook] : $subjects[$hook]->getHooks();
    } catch ( Exception $exc ) {
        return errorFromException( $exc );
    }
}

function getHook( $hook, $id ) {
    $hooks = $hooks instanceof Subject ? $hooks : getHooks( $hook, TRUE );
    if ( is_wp_error( $hooks ) ) {
        return $hooks;
    }
    try {
        return $hooks->getHook( $id );
    } catch ( Exception $exc ) {
        return errorFromException( $exc );
    }
}

function addHook( $id, $args, $hook = '', $is_filter = FALSE ) {
    if ( ( ! is_string( $hook ) && ! is_array( $hook ) ) || empty( $hook ) ) {
        return new \WP_Error( 'hooks-bad-hook-id' );
    }
    if ( ! is_string( $id ) || empty( $id ) ) {
        return new \WP_Error( 'hooks-bad-hook' );
    }
    try {
        $container = Container::instance();
        $hookObject = $container->get( 'striatum.hook' )->prepare( $args );
        $singular = is_string( $hook ) && ( substr_count( $hook, ',' ) === 0 );
        $subjects = $container->get( 'striatum.manager' )->addSubjects( $hook, $is_filter );
        $added = [ ];
        foreach ( $subjects as $subject ) {
            if ( ! $subject instanceof Subject ) {
                return new \WP_Error( 'hooks-bad-subject' );
            }
            $exists = getHook( $hook, $id );
            if ( $exists instanceof Hook ) {
                $added[] = updateHook( $hook, $exists, $args );
            } else {
                $hookObject->setId( $id );
                $hookObject->setSubject( $subject );
                $added[] = $subject->attach( $hookObject );
            }
        }
        return $singular ? array_shift( $added ) : $added;
    } catch ( Exception $exc ) {
        return errorFromException( $exc );
    }
}

function updateHook( $hook, $id, $args = [ ], $new_as_filter = FALSE ) {
    if ( ! is_string( $hook ) || empty( $hook ) ) {
        return new \WP_Error( 'hooks-bad-hook' );
    }
    if ( ( ! is_string( $id ) && ! $id instanceof Hook ) || empty( $hook ) ) {
        return new \WP_Error( 'hooks-bad-hook-id' );
    }
    $hookObject = $id;
    if ( is_string( $hookObject ) ) {
        $hookObject = getHook( $hook, $hookObject );
        if ( is_wp_error( $hookObject ) ) {
            return $hookObject;
        } elseif ( is_null( $hookObject ) ) {
            return addHook( $id, $args, $hook, $new_as_filter );
        }
    }
    if ( $hookObject instanceof Hook ) {
        return $hookObject->prepare( $args );
    }
}

function addFilter( $id, $hook, $callback, $priority = 10, $args_num = 1, $times = 0 ) {
    $args = compact( 'callback', 'priority', 'args_num', 'times' );
    return addHook( $id, $args, $hook, TRUE );
}

function addAction( $id, $hook, $callback, $priority = 10, $args_num = 1, $times = 0 ) {
    $args = compact( 'callback', 'priority', 'args_num', 'times' );
    return addHook( $id, $args, $hook, FALSE );
}

function removeHook( $hook, $id ) {
    try {
        $hookObject = NULL;
        $subject = Container::instance()->get( 'striatum.manager' )->getSubject( $hook );
        if ( $subject instanceof Subject ) {
            $hookObject = $subject->getHook( $id );
        }
        if ( $hookObject instanceof Hook ) {
            $subject->detach( $hookObject );
        }
    } catch ( Exception $exc ) {
        return errorFromException( $exc );
    }
}

function removeHooks( $hooks ) {
    try {
        Container::instance()->get( 'striatum.manager' )->removeSubjects( $hooks );
    } catch ( Exception $exc ) {
        return errorFromException( $exc );
    }
}

function freezeHooks( $hooks ) {
    try {
        Container::instance()->get( 'striatum.manager' )->freezeSubjects( $hooks );
    } catch ( Exception $exc ) {
        return errorFromException( $exc );
    }
}

function unfreezeHooks( $hooks ) {
    try {
        Container::instance()->get( 'striatum.manager' )->unfreezeSubjects( $hooks );
    } catch ( Exception $exc ) {
        return errorFromException( $exc );
    }
}

function trigger( $hook, Array $args = [ ] ) {
    $hooks = getHooks( $hook, TRUE );
    if ( is_wp_error( $hooks ) ) {
        return $hooks;
    }
    try {
        $hooks->setArgs( $args );
        $result = $hooks->notify();
        if ( $hooks->isFilter() ) return $result;
    } catch ( Exception $exc ) {
        if ( $hooks->isFilter() ) return $args[0];
    }
}

function filter( $hook, $subject = NULL, Array $args = [ ] ) {
    array_unshift( $args, $subject );
    return trigger( $hook, $args );
}

function hookHas( $hook, $id ) {
    $hooks = $hook instanceof Subject ? $hook : getHooks( $hook );
    if ( is_wp_error( $hook ) ) {
        return $hook;
    }
    if ( ! $hooks instanceof Subject ) {
        return FALSE;
    }
    return $hooks->getHook( $id ) instanceof Hook;
}

function actionHas( $hook, $id ) {
    $hooks = getHooks( $hook, TRUE );
    if ( is_wp_error( $hooks ) ) {
        return $hooks;
    }
    if ( ! $hooks instanceof Subject ) {
        return FALSE;
    }
    return hookHas( $hooks, $id ) && ! $hooks->isFilter();
}

function filterHas( $hook, $id ) {
    $hooks = getHooks( $hook, TRUE );
    if ( is_wp_error( $hooks ) ) {
        return $hooks;
    }
    if ( ! $hooks instanceof Subject ) {
        return FALSE;
    }
    return hookHas( $hooks, $id ) && $hooks->isFilter();
}

function callbackDoing( $hook, $id ) {
    $hooks = getHooks( $hook );
    if ( is_wp_error( $hooks ) ) {
        return $hooks;
    }
    return in_array( $id, (array) $hooks->calling );
}

function callbackDone( $hook, $id ) {
    $hooks = getHooks( $hook );
    if ( is_wp_error( $hooks ) ) {
        return $hooks;
    }
    return $hooks instanceof Subject ? in_array( $id, (array) $hooks->called ) : FALSE;
}

function callbackLast( $hook ) {
    $hooks = getHooks( $hook );
    if ( is_wp_error( $hooks ) ) {
        return $hooks;
    }
    return $hooks instanceof Subject ? $hooks->last_callback : NULL;
}

function callbackLastArgs( $hook, $id ) {
    $hook = getHook( $hook, $id );
    if ( is_wp_error( $hook ) ) {
        return $hook;
    }
    return $hook instanceof Hook ? $hook->get( 'last_args' ) : NULL;
}

function doingPriority( $hook ) {
    $hooks = getHooks( $hook );
    if ( is_wp_error( $hooks ) ) {
        return $hooks;
    }
    return $hooks instanceof Subject ? $hooks->priority_now : FALSE;
}

if ( ! function_exists( 'Brain\errorFromException' ) ) {

    function errorFromException( Exception $exc ) {
        $name = get_class( $exc );
        $name .= $exc->getCode() ? '-' . $exc->getCode() : '';
        $msg = $exc->getMessage() ? : '';
        return WP_Error( 'brain-exception-' . $name, $msg );
    }

}
