<?php namespace Brain\Striatum\Tests\Functional;

use Brain\HooksMock\HooksMock as H;

class APITest extends \Brain\Striatum\Tests\FunctionalTestCase {

    private function api() {
        return $this->getBrain()->get( 'hooks.api' );
    }

    private function addSomeHooks() {
        $stub = new \Brain\Striatum\Tests\ContextStub();
        $cbs = [
            'init.h'        => '__return_true',
            'the_title.h'   => '__return_empty_string',
            'save_post.h'   => '__return_true',
            'the_content.h' => [ $stub, 'setContext' ],
            'wp_head.h'     => [ __CLASS__, 'testGetHooks' ]
        ];
        $t = 0;
        foreach ( $cbs as $id => $cb ) {
            $t ++;
            $hook = str_replace( '.h', '', $id );
            $is_filter = $t % 2 === 0;
            $args = [ 'callback' => $cb, 'priority' => $t, 'args_num' => $t + 1, 'times' => $t ];
            $added = $this->api()->addHook( $id, $args, $hook, $is_filter );
            $type = $is_filter ? 'filter' : 'action';
            assertTrue( H::hasHook( $type, $hook, [ $added, 'proxy' ], $t ) );
        }
        return $cbs;
    }

    function testGetHooksFailsIfBadHook() {
        assertIsWPError( $this->api()->getHooks( TRUE ) );
    }

    function testGetHooksNullIfHookNotExists() {
        assertNull( $this->api()->getHooks( 'foo' ) );
    }

    function testGetHooks() {
        $cbs = $this->addSomeHooks();
        $i = 0;
        foreach ( array_keys( $cbs ) as $_hook_id ) {
            $i ++;
            $hooks = $this->api()->getHooks( str_replace( '.h', '', $_hook_id ) );
            assertTrue( is_array( $hooks ) );
            foreach ( $hooks as $hook_id => $hook ) {
                assertInstanceOf( '\Brain\Striatum\HookInterface', $hook );
                assertEquals( $_hook_id, $hook_id );
                assertEquals( $hook_id, $hook->getId() );
                assertEquals( $cbs[$hook_id], $hook->callback );
                assertEquals( $i, $hook->times );
                assertEquals( $i + 1, $hook->args_num );
                assertEquals( $i, $hook->priority );
            }
        }
    }

    function testGetHook() {
        $cbs = $this->addSomeHooks();
        $i = 0;
        foreach ( array_keys( $cbs ) as $id ) {
            $i ++;
            $hook = $this->api()->getHook( str_replace( '.h', '', $id ), $id );
            assertInstanceOf( '\Brain\Striatum\HookInterface', $hook );
            assertEquals( $id, $hook->getId() );
            assertEquals( $cbs[$id], $hook->callback );
            assertEquals( $i, $hook->times );
            assertEquals( $i + 1, $hook->args_num );
            assertEquals( $i, $hook->priority );
        }
    }

    function testAddHookFailsIfBadID() {
        assertIsWPError( $this->api()->addHook( TRUE ) );
    }

    function testAddHookFailsIfBadHook() {
        assertIsWPError( $this->api()->addHook( 'foo', [ ], TRUE ) );
    }

    function testAddHookSingleAction() {
        $args = [ 'callback' => '__return_true', 'priority' => 10, 'args_num' => 1, 'times' => 0 ];
        $hook = $this->api()->addHook( 'foo.test', $args, 'action_1' );
        assertInstanceOf( '\Brain\Striatum\HookInterface', $hook );
        assertTrue( H::hasAction( 'action_1', [ $hook, 'proxy' ], 10 ) );
        assertTrue( $this->api()->actionHas( 'action_1', 'foo.test' ) );
        assertEquals( 'foo.test', $hook->getId() );
        assertEquals( '__return_true', $hook->callback );
        assertEquals( 0, $hook->times );
        assertEquals( 1, $hook->args_num );
        assertEquals( 10, $hook->priority );
    }

    function testAddHookMultiAction() {
        $args = [ 'callback' => '__return_true' ];
        $added_hooks = $this->api()->addHook( 'foo.test', $args, 'action_1 | action_2 | action_3' );
        assertTrue( is_array( $added_hooks ) );
        foreach ( [ 'action_1', 'action_2', 'action_3' ] as $action ) {
            assertTrue( $this->api()->actionHas( $action, 'foo.test' ) );
        }
        foreach ( $added_hooks as $hook ) {
            assertInstanceOf( '\Brain\Striatum\HookInterface', $hook );
            $action = $hook->getSubject()->getId();
            assertTrue( H::hasAction( $action, [ $hook, 'proxy' ], 10 ) );
            assertEquals( 'foo.test', $hook->getId() );
            assertEquals( '__return_true', $hook->callback );
            assertEquals( 0, $hook->times );
            assertEquals( 1, $hook->args_num );
            assertEquals( 10, $hook->priority );
        }
    }

    function testAddHookSingleFilter() {
        $args = [ 'callback' => '__return_true', 'priority' => 10, 'args_num' => 1, 'times' => 0 ];
        $hook = $this->api()->addHook( 'foo.test', $args, 'filter_1', TRUE );
        assertInstanceOf( '\Brain\Striatum\HookInterface', $hook );
        assertTrue( $this->api()->filterHas( 'filter_1', 'foo.test' ) );
        assertTrue( H::hasFilter( 'filter_1', [ $hook, 'proxy' ], 10 ) );
        assertEquals( 'foo.test', $hook->getId() );
        assertEquals( '__return_true', $hook->callback );
        assertEquals( 0, $hook->times );
        assertEquals( 1, $hook->args_num );
        assertEquals( 10, $hook->priority );
    }

    function testAddHookMultiFilter() {
        $args = [ 'callback' => '__return_true' ];
        $added_hooks = $this->api()->addHook( 'foo.test', $args, [ 'filter_1', 'filter_2' ], TRUE );
        assertTrue( is_array( $added_hooks ) );
        foreach ( [ 'filter_1', 'filter_2' ] as $filter ) {
            assertTrue( $this->api()->filterHas( $filter, 'foo.test' ) );
        }
        foreach ( $added_hooks as $hook ) {
            assertInstanceOf( '\Brain\Striatum\HookInterface', $hook );
            $filter = $hook->getSubject()->getId();
            assertTrue( H::hasFilter( $filter, [ $hook, 'proxy' ], 10 ) );
            assertEquals( 'foo.test', $hook->getId() );
            assertEquals( '__return_true', $hook->callback );
            assertEquals( 0, $hook->times );
            assertEquals( 1, $hook->args_num );
            assertEquals( 10, $hook->priority );
        }
    }

    function testUpdateHookFailsIfBadHook() {
        assertIsWPError( $this->api()->updateHook( TRUE ) );
    }

    function testUpdateHookFailsIfHookBadObserver() {
        assertIsWPError( $this->api()->updateHook( 'foo', TRUE ) );
    }

    function testUpdateHookFromString() {
        $args = [ 'callback' => '__return_false', 'priority' => 20, 'args_num' => 2, 'times' => 2 ];
        $this->api()->addHook( 'foo.test', $args, 'filter_1', TRUE );
        $hook = $this->api()->updateHook( 'filter_1', 'foo.test', [ 'priority' => 30, 'times' => 3 ] );
        assertInstanceOf( '\Brain\Striatum\HookInterface', $hook );
        assertTrue( H::hasFilter( 'filter_1', [ $hook, 'proxy' ] ) );
        assertEquals( 'foo.test', $hook->getId() );
        assertEquals( '__return_false', $hook->callback );
        assertEquals( 2, $hook->args_num );
        assertEquals( 3, $hook->times );
        assertEquals( 30, $hook->priority );
    }

    function testUpdateHookFromObject() {
        $args = [ 'callback' => '__return_false', 'priority' => 20, 'args_num' => 2 ];
        $this->api()->addHook( 'foo.test', $args, 'filter_4', TRUE );
        $old = $this->api()->getHook( 'filter_4', 'foo.test' )->runTwice();
        $hook = $this->api()->updateHook( 'filter_4', $old, [ 'callback' => '__return_true' ] );
        assertInstanceOf( '\Brain\Striatum\HookInterface', $hook );
        assertTrue( H::hasFilter( 'filter_4', [ $hook, 'proxy' ], 20 ) );
        assertEquals( 'foo.test', $hook->getId() );
        assertEquals( '__return_true', $hook->callback );
        assertEquals( 2, $hook->args_num );
        assertEquals( 2, $hook->times );
        assertEquals( 20, $hook->priority );
    }

    function testRemoveHook() {
        $args = [ 'callback' => '__return_false', 'priority' => 20, 'args_num' => 2 ];
        $hook = $this->api()->addHook( 'foo.test', $args, 'filter_1', TRUE );
        assertTrue( H::hasFilter( 'filter_1', [ $hook, 'proxy' ], 20 ) );
        $this->api()->removeHook( 'filter_1', 'foo.test' );
        assertNull( $this->api()->getHook( 'filter_1', 'foo.test' ) );
        assertFalse( H::hasFilter( 'filter_1', [ $hook, 'proxy' ] ) );
    }

    function testRemoveHooksSingle() {
        $args = [ 'callback' => '__return_false', 'priority' => 20, 'args_num' => 2 ];
        $hook = $this->api()->addHook( 'foo.test', $args, 'action_4', FALSE );
        assertTrue( H::hasAction( 'action_4', [ $hook, 'proxy' ], 20 ) );
        $this->api()->removeHooks( 'action_4' );
        assertNull( $this->api()->getHooks( 'action_4' ) );
        assertFalse( H::hasAction( 'action_4' ) );
    }

    function testRemoveHooksMulti() {
        $cbs = $this->addSomeHooks();
        $hooks = str_replace( '.h', '', implode( '|', array_keys( $cbs ) ) );
        $this->api()->removeHooks( $hooks );
        foreach ( array_keys( $cbs ) as $id ) {
            $filters = [ 'the_title', 'the_content' ];
            $tag = str_replace( '.h', '', $id );
            $type = in_array( $tag, $filters, TRUE ) ? 'filter' : 'action';
            assertNull( $this->api()->getHooks( $tag ) );
            assertFalse( H::hasHook( $type, $tag ) );
        }
    }

    function testFreezeHooksSingle() {
        $args = [ 'callback' => '__return_false', 'priority' => 20, 'args_num' => 2 ];
        $hook = $this->api()->addHook( 'foo.test', $args, 'action_5', FALSE );
        assertTrue( H::hasAction( 'action_5', [ $hook, 'proxy' ], 20 ) );
        $this->api()->freezeHooks( 'action_5' );
        $man = $this->getBrain()->get( 'striatum.manager' );
        assertNull( $this->api()->getHooks( 'action_5' ) );
        assertInstanceOf( 'Brain\Striatum\SubjectInterface', $man->getFrozenSubject( 'action_5' ) );
        assertFalse( H::hasAction( 'action_5' ) );
    }

    function testFreezeHooksMulti() {
        $cbs = $this->addSomeHooks();
        $hooks = str_replace( '.h', '', implode( '|', array_keys( $cbs ) ) );
        $this->api()->freezeHooks( $hooks );
        $man = $this->getBrain()->get( 'striatum.manager' );
        foreach ( array_keys( $cbs ) as $id ) {
            $hook = str_replace( '.h', '', $id );
            $filters = [ 'the_title', 'the_content' ];
            $type = in_array( $hook, $filters, TRUE ) ? 'filter' : 'action';
            assertNull( $this->api()->getHooks( $hook ) );
            assertInstanceOf( 'Brain\Striatum\SubjectInterface', $man->getFrozenSubject( $hook ) );
            assertFalse( H::hasHook( $type, $hook ) );
        }
    }

    function testUnfreezeHooksSingle() {
        $args = [ 'callback' => '__return_false', 'priority' => 20, 'args_num' => 2 ];
        $added = $this->api()->addHook( 'foo.test', $args, 'action_5', FALSE );
        $man = $this->getBrain()->get( 'striatum.manager' );
        $interface = 'Brain\Striatum\SubjectInterface';
        assertTrue( H::hasAction( 'action_5', [ $added, 'proxy' ] ) );
        $this->api()->freezeHooks( 'action_5' );
        assertNull( $this->api()->getHooks( 'action_5' ) );
        assertFalse( H::hasAction( 'action_5' ) );
        assertInstanceOf( $interface, $man->getFrozenSubject( 'action_5' ) );
        $this->api()->unfreezeHooks( 'action_5' );
        assertInstanceOf( $interface, $this->api()->getHooks( 'action_5', TRUE ) );
        assertNull( $man->getFrozenSubject( 'action_5' ) );
        assertTrue( H::hasAction( 'action_5', [ $added, 'proxy' ] ) );
    }

    function testUnfreezeHooksMulti() {
        $cbs = $this->addSomeHooks();
        $hooks = str_replace( '.h', '', implode( '|', array_keys( $cbs ) ) );
        $this->api()->freezeHooks( $hooks );
        $man = $this->getBrain()->get( 'striatum.manager' );
        $interface = 'Brain\Striatum\SubjectInterface';
        $filters = [ 'the_title', 'the_content' ];
        foreach ( array_keys( $cbs ) as $id ) {
            $hook = str_replace( '.h', '', $id );
            assertNull( $this->api()->getHooks( $hook ) );
            assertInstanceOf( $interface, $man->getFrozenSubject( $hook ) );
            $type = in_array( $hook, $filters, TRUE ) ? 'filter' : 'action';
            assertFalse( H::hasHook( $type, $hook ) );
        }
        $this->api()->unfreezeHooks( $hooks );
        foreach ( array_keys( $cbs ) as $id ) {
            $hook = str_replace( '.h', '', $id );
            assertNull( $man->getFrozenSubject( $hook ) );
            $subject = $this->api()->getHooks( $hook, TRUE );
            assertInstanceOf( $interface, $subject );
            $unfreezed = $subject->getHook( $id );
            $type = in_array( $hook, $filters, TRUE ) ? 'filter' : 'action';
            assertTrue( H::hasHook( $type, $hook, [ $unfreezed, 'proxy' ] ) );
        }
    }

    function testTriggerAction() {
        $args = [ 'callback' => '__return_false', 'priority' => 20, 'args_num' => 2 ];
        $foo = $this->api()->addHook( 'foo.test', $args, 'action_6' );
        $bar = $this->api()->addHook( 'bar.test', $args, 'action_6' );
        assertTrue( H::hasAction( 'action_6', [ $foo, 'proxy' ], 20 ) );
        assertTrue( H::hasAction( 'action_6', [ $bar, 'proxy' ], 20 ) );
        $result = $this->api()->trigger( 'action_6', 'foo', 'bar', 'baz' );
        assertNull( $result );
        H::assertActionFired( 'action_6', [ 'foo', 'bar', 'baz' ] );
    }

    function testTriggerFilter() {
        $args = [ 'callback' => '__return_true', 'priority' => 30, 'args_num' => 1 ];
        $foo = $this->api()->addHook( 'foo.test', $args, 'filter_foo', TRUE );
        $bar = $this->api()->addHook( 'bar.test', $args, 'filter_foo', TRUE );
        assertTrue( H::hasFilter( 'filter_foo', [ $foo, 'proxy' ], 30 ) );
        assertTrue( H::hasFilter( 'filter_foo', [ $bar, 'proxy' ], 30 ) );
        $result = $this->api()->trigger( 'filter_foo', 'foo', 'bar', 'baz' );
        assertEquals( [ 'filter', 'filter_foo', [ 'foo', 'bar', 'baz' ] ], $result );
        H::assertFilterFired( 'filter_foo', [ 'foo', 'bar', 'baz' ] );
    }

    function testFilter() {
        $args = [ 'callback' => '__return_true', 'priority' => 30, 'args_num' => 1 ];
        $foo = $this->api()->addHook( 'foo.test', $args, 'filter_foo', TRUE );
        $bar = $this->api()->addHook( 'bar.test', $args, 'filter_foo', TRUE );
        assertTrue( H::hasFilter( 'filter_foo', [ $foo, 'proxy' ], 30 ) );
        assertTrue( H::hasFilter( 'filter_foo', [ $bar, 'proxy' ], 30 ) );
        $result = $this->api()->filter( 'filter_foo', 'foo' );
        assertEquals( [ 'filter', 'filter_foo', [ 'foo' ] ], $result );
        H::assertFilterFired( 'filter_foo', [ 'foo' ] );
    }

    function testFilterReturnActualIfNoHooks() {
        $result = $this->api()->filter( 'filter_foo', 'foo' );
        assertEquals( 'foo', $result );
    }

    function testHookHasFailsIfBadHook() {
        assertIsWPError( $this->api()->hookHas() );
    }

    function testHookHasFalseIfHookNotExists() {
        assertFalse( $this->api()->hookHas( 'foo' ) );
    }

    function testHookHas() {
        $foo = $this->api()->addHook( 'foo.test', ['callback' => '__return_true' ], 'action_foo' );
        assertTrue( H::hasAction( 'action_foo', [ $foo, 'proxy' ] ) );
        assertTrue( $this->api()->hookHas( 'action_foo', 'foo.test' ) );
    }

    function testHookHasReturnHook() {
        $foo = $this->api()->addHook( 'foo', [ 'callback' => '__return_true' ], 'a_filter', TRUE );
        assertTrue( H::hasFilter( 'a_filter', [ $foo, 'proxy' ] ) );
        $hook = $this->api()->hookHas( 'a_filter', 'foo', TRUE );
        assertInstanceOf( 'Brain\Striatum\HookInterface', $hook );
        assertEquals( 'foo', $hook->getId() );
        assertEquals( 'a_filter', $hook->getSubject()->getId() );
        assertTrue( $hook->getSubject()->isFilter() );
    }

    function testCallbackDoneFailsIfBadHook() {
        assertIsWPError( $this->api()->callbackDone() );
    }

    function testCallbackDoneFailsIfBadId() {
        assertIsWPError( $this->api()->callbackDone( 'foo' ) );
    }

    function testCallbackDoneFalse() {
        $foo = $this->api()->addHook( 'foo', [ 'callback' => '__return_true' ], 'a_filter', TRUE );
        assertTrue( H::hasFilter( 'a_filter', [ $foo, 'proxy' ] ) );
        assertFalse( $this->api()->callbackDone( 'a_filter', 'foo' ) );
    }

    function testCallbackDone() {
        $foo = $this->api()->addHook( 'foo', [ 'callback' => 'strlen' ], 'a_filter', TRUE );
        assertTrue( H::hasFilter( 'a_filter', [ $foo, 'proxy' ] ) );
        $result = $foo->proxy( 'foo', 'loooooong_string' );
        assertEquals( $result, 3 );
        assertTrue( $this->api()->callbackDone( 'a_filter', 'foo' ) );
    }

    function testxIsDoingCallbackFailsIfBadHook() {
        assertIsWPError( $this->api()->doingCallback() );
    }

    function testIsDoingCallbackFailsIfBadId() {
        assertIsWPError( $this->api()->doingCallback( 'foo' ) );
    }

    function testIsDoingCallback() {
        $doing = FALSE;
        $cb = function() use( &$doing ) {
            $doing = (bool) $this->api()->doingCallback( 'an_action', 'foo' );
        };
        $test = $this->api()->addHook( 'foo', [ 'callback' => $cb ], 'an_action' );
        $test->proxy();
        assertTrue( $doing );
    }

    function testCallbackLast() {
        $duplicate = function( $n ) {
            return $n * 2;
        };
        $n3 = $this->api()->addHook( 'foo', [ 'callback' => 'strlen' ], 'a_filter', TRUE );
        $n6 = $this->api()->addHook( 'foo_duple', [ 'callback' => $duplicate ], 'a_filter', TRUE );
        assertTrue( H::hasFilter( 'a_filter', [ $n3, 'proxy' ] ) );
        assertTrue( H::hasFilter( 'a_filter', [ $n6, 'proxy' ] ) );
        $result = $n6->proxy( $n3->proxy( 'foo', 'loooooong_string' ) );
        assertEquals( $result, 6 );
        assertTrue( $this->api()->callbackDone( 'a_filter', 'foo' ) );
        assertTrue( $this->api()->callbackDone( 'a_filter', 'foo_duple' ) );
        assertEquals( 'foo_duple', $this->api()->callbackLast( 'a_filter' ) );
    }

    function testCallbackLastArgsFailsIfBadHook() {
        assertIsWPError( $this->api()->callbackLastArgs() );
    }

    function testCallbackLastArgsFailsIfBadId() {
        assertIsWPError( $this->api()->callbackLastArgs( 'foo' ) );
    }

    function testCallbackLastArgs() {
        $args = [ 'callback' => '__return_true', 'args_num' => 2 ];
        $foo = $this->api()->addHook( 'foo', $args, 'a_filter', TRUE );
        assertTrue( H::hasFilter( 'a_filter', [ $foo, 'proxy' ] ) );
        $foo->proxy( 'first', 'second', 'third' );
        assertEquals( [ 'first', 'second' ], $this->api()->callbackLastArgs( 'a_filter', 'foo' ) );
    }

    function testDoingPriorityFailsIfBadHook() {
        assertIsWPError( $this->api()->doingPriority() );
    }

    function testDoingPriority() {
        $doing = -1;
        $cb = function() use( &$doing ) {
            $doing = (int) $this->api()->doingPriority( 'foo' );
        };
        $test = $this->api()->addHook( 'foo', [ 'callback' => $cb, 'priority' => 33 ], 'foo' );
        $test->proxy();
        assertEquals( 33, $doing );
    }

}