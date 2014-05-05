<?php namespace Brain\Striatum\Tests\Unit;

use Brain\Striatum\Tests\TestCase;
use Brain\Striatum\Tests\HooksMock;

class HooksMockTest extends TestCase {

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testAddHookFailsIfEmptyHook() {
        add_action();
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testAddHookFailsIfBadHook() {
        add_action( TRUE );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testAddHookFailsIfBadCallback() {
        add_action( 'foo', 'this_callback_does_not_exists', 20, 3 );
    }

    function testAddHookOnAddAction() {
        $stub = new \Brain\Striatum\Tests\ContextStub;
        add_action( 'foo', '__return_true', 20, 3 );
        add_action( 'bar', '__return_empty_string', 10, 4 );
        add_action( 'foo', [ __CLASS__, __FUNCTION__ ], 10 );
        add_action( 'foo', [ $stub, 'setContext' ], 10, 2 );
        $cbidthis = HooksMock::callbackUniqueId( [ __CLASS__, __FUNCTION__ ] );
        $cbidstub = HooksMock::callbackUniqueId( [ $stub, 'setContext' ] );
        $actions = [
            'foo' => [
                20 => [
                    '__return_true' => [ 'cb' => '__return_true', 'num_args' => 3 ]
                ],
                10 => [
                    $cbidthis => [ 'cb' => [ __CLASS__, __FUNCTION__ ], 'num_args' => 1 ],
                    $cbidstub => [ 'cb' => [ $stub, 'setContext' ], 'num_args' => 2 ]
                ]
            ],
            'bar' => [
                10 => [
                    '__return_empty_string' => [ 'cb' => '__return_empty_string', 'num_args' => 4 ]
                ]
            ]
        ];
        assertEquals( [ 'actions' => $actions, 'filters' => [ ] ], HooksMock::$hooks );
    }

    function testAddHookOnAddFilter() {
        $stub = new \Brain\Striatum\Tests\ContextStub;
        add_filter( 'foo', '__return_true', 20, 3 );
        add_filter( 'bar', '__return_empty_string', 10, 4 );
        add_filter( 'foo', [ __CLASS__, __FUNCTION__ ], 10 );
        add_filter( 'foo', [ $stub, 'setContext' ], 10, 2 );
        $cbidthis = HooksMock::callbackUniqueId( [ __CLASS__, __FUNCTION__ ] );
        $cbidstub = HooksMock::callbackUniqueId( [ $stub, 'setContext' ] );
        $filters = [
            'foo' => [
                20 => [
                    '__return_true' => [ 'cb' => '__return_true', 'num_args' => 3 ]
                ],
                10 => [
                    $cbidthis => [ 'cb' => [ __CLASS__, __FUNCTION__ ], 'num_args' => 1 ],
                    $cbidstub => [ 'cb' => [ $stub, 'setContext' ], 'num_args' => 2 ]
                ]
            ],
            'bar' => [
                10 => [
                    '__return_empty_string' => [ 'cb' => '__return_empty_string', 'num_args' => 4 ]
                ]
            ]
        ];
        assertEquals( [ 'actions' => [ ], 'filters' => $filters ], HooksMock::$hooks );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testRemoveHookFailsIfEmptyHook() {
        remove_action();
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testRemoveHookFailsIfBadHook() {
        remove_action( TRUE );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testRemoveHookFailsIfBadCallback() {
        remove_action( 'foo', 'this_callback_does_not_exists', 20, 3 );
    }

    function testRemoveHook() {
        $stub = new \Brain\Striatum\Tests\ContextStub;
        add_filter( 'foo', '__return_true', 20, 3 );
        add_action( 'bar', '__return_empty_string', 10, 4 );
        add_filter( 'foo', [ __CLASS__, __FUNCTION__ ], 30 );
        add_action( 'foo', [ $stub, 'setContext' ], 20, 2 );
        remove_filter( 'foo', '__return_true', 20, 3 );
        remove_action( 'bar', '__return_empty_string' );
        remove_filter( 'foo', [ __CLASS__, __FUNCTION__ ], 30, 1 );
        remove_action( 'foo', [ $stub, 'setContext' ], 20 );
        assertEquals( [ 'actions' => [ ], 'filters' => [ ] ], HooksMock::$hooks );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testFireHookFailsIfEmptyHook() {
        do_action();
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testFireHookFailsIfBadHook() {
        apply_filters( TRUE );
    }

    function testFireHookOnDoAction() {
        do_action( 'hook1', 'foo', [ 'foo', 'bar' ], TRUE );
        do_action( 'hook1', TRUE );
        do_action( 'hook2', [ 'foo', 'bar' ] );
        do_action( 'hook3' );
        do_action( 'hook3', [ 'foo', 'bar' ] );
        $actions = [
            'hook1' => [
                [ 'foo', [ 'foo', 'bar' ], TRUE ],
                [ TRUE ]
            ],
            'hook2' => [
                [ [ 'foo', 'bar' ] ]
            ],
            'hook3' => [
                [ ],
                [ [ 'foo', 'bar' ] ]
            ]
        ];
        assertEquals( [ 'actions' => $actions, 'filters' => [ ] ], HooksMock::$hooks_done );
    }

    function testFireHookOnApplyFilters() {
        apply_filters( 'hook1', 'actual', 'foo', [ 'foo', 'bar' ], TRUE );
        apply_filters( 'hook1', 'actual', TRUE );
        apply_filters( 'hook2', 'actual', [ 'foo', 'bar' ] );
        apply_filters( 'hook3', 'actual' );
        apply_filters( 'hook3', 'actual', [ 'foo', 'bar' ] );
        $filters = [
            'hook1' => [
                [ 'actual', 'foo', [ 'foo', 'bar' ], TRUE ],
                [ 'actual', TRUE ]
            ],
            'hook2' => [
                [ 'actual', [ 'foo', 'bar' ] ]
            ],
            'hook3' => [
                [ 'actual' ],
                [ 'actual', [ 'foo', 'bar' ] ]
            ]
        ];
        assertEquals( [ 'actions' => [ ], 'filters' => $filters ], HooksMock::$hooks_done );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testCallbackUniqueIdFailsIfBadCallback() {
        HooksMock::callbackUniqueId( 'foo' );
    }

    function testCallbackUniqueId() {
        $static = __CLASS__ . '::' . __FUNCTION__;
        $stub = new \Brain\Striatum\Tests\ContextStub;
        $dinamyc = spl_object_hash( $stub ) . 'setContext';
        $func = function( $foo = 1 ) {
            return $foo;
        };
        assertEquals( '__return_false', HooksMock::callbackUniqueId( '__return_false' ) );
        assertEquals( $static, HooksMock::callbackUniqueId( [ __CLASS__, __FUNCTION__ ] ) );
        assertEquals( $dinamyc, HooksMock::callbackUniqueId( [ $stub, 'setContext' ] ) );
        assertEquals( spl_object_hash( $func ), HooksMock::callbackUniqueId( $func ) );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testHasHookFailsIfBadHook() {
        HooksMock::hasHook( 'action', TRUE );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testHasHookFailsIfBadCallable() {
        HooksMock::hasHook( 'action', 'foo', 'foo' );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testHasHookFailsIfBadPriority() {
        HooksMock::hasHook( 'action', 'foo', '__return_false', 'foo' );
    }

    function testHasHook() {
        $stub = new \Brain\Striatum\Tests\ContextStub;
        add_filter( 'foo', '__return_true', 20, 3 );
        add_action( 'bar', '__return_empty_string', 10, 4 );
        add_filter( 'foo', [ __CLASS__, __FUNCTION__ ], 30 );
        add_action( 'foo', [ $stub, 'setContext' ], 20, 2 );
        assertTrue( HooksMock::hasHook( 'filter', 'foo', '__return_true', 20 ) );
        assertTrue( HooksMock::hasHook( 'action', 'bar', '__return_empty_string' ) );
        assertTrue( HooksMock::hasHook( 'filter', 'foo', [ __CLASS__, __FUNCTION__ ], 30 ) );
        assertTrue( HooksMock::hasHook( 'action', 'foo', [ $stub, 'setContext' ], 20 ) );
        assertFalse( HooksMock::hasHook( 'filter', 'foo', '__return_true', 30 ) );
        assertFalse( HooksMock::hasHook( 'action', 'bar', '__return_true' ) );
        assertFalse( HooksMock::hasHook( 'action', 'baz', '__return_true' ) );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testAssertHookAddedIfBadHook() {
        HooksMock::assertHookAdded( 'action', TRUE );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testAssertHookAddedIfBadCallback() {
        HooksMock::assertHookAdded( 'action', 'foo', 2 );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testAssertHookAddedThrowIfNoHook() {
        HooksMock::assertHookAdded( 'action', 'foo', '__return_true' );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testAssertHookAddedIfBadPriority() {
        $actions = [
            'foo' => [
                10 => [
                    '__return_true' => [ 'cb' => [ '__return_true' ], 'num_args' => 1 ],
                ]
            ]
        ];
        $filters = [
            'bar' => [
                20 => [
                    '__return_empty_string' => [ 'cb' => '__return_empty_string', 'num_args' => 2 ]
                ]
            ]
        ];
        HooksMock::$hooks = [ 'actions' => $actions, 'filters' => $filters ];
        HooksMock::assertHookAdded( 'action', 'foo', '__return_true', 'foo' );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testAssertHookAddedThrowIfNoPriority() {
        $actions = [
            'foo' => [
                10 => [
                    '__return_true' => [ 'cb' => [ '__return_true' ], 'num_args' => 1 ],
                ]
            ]
        ];
        $filters = [
            'bar' => [
                20 => [
                    '__return_empty_string' => [ 'cb' => '__return_empty_string', 'num_args' => 2 ]
                ]
            ]
        ];
        HooksMock::$hooks = [ 'actions' => $actions, 'filters' => $filters ];
        HooksMock::assertHookAdded( 'filter', 'bar', '__return_empty_string' );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testAssertHookAddedThrowIfNoCallback() {
        $actions = [
            'foo' => [
                10 => [
                    '__return_true' => [ 'cb' => [ '__return_true' ], 'num_args' => 1 ],
                ]
            ]
        ];
        $filters = [
            'bar' => [
                20 => [
                    '__return_empty_string' => [ 'cb' => '__return_empty_string', 'num_args' => 2 ]
                ]
            ]
        ];
        HooksMock::$hooks = [ 'actions' => $actions, 'filters' => $filters ];
        HooksMock::assertHookAdded( 'action', 'foo', '__return_empty_string', 10 );
    }

    function testAssertHookAddedNotThrowIfNoArgs() {
        $actions = [
            'foo' => [
                10 => [
                    '__return_true' => [ 'cb' => [ '__return_true' ], 'num_args' => 1 ],
                ]
            ]
        ];
        $filters = [
            'bar' => [
                20 => [
                    '__return_empty_string' => [ 'cb' => '__return_empty_string', 'num_args' => 2 ]
                ]
            ]
        ];
        HooksMock::$hooks = [ 'actions' => $actions, 'filters' => $filters ];
        HooksMock::assertHookAdded( 'action', 'foo', '__return_true', 10 );
        HooksMock::assertHookAdded( 'filter', 'bar', '__return_empty_string', 20 );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testAssertHookAddedThrowIfWrongArgs() {
        $actions = [
            'foo' => [
                10 => [
                    '__return_true' => [ 'cb' => [ '__return_true' ], 'num_args' => 1 ],
                ]
            ]
        ];
        $filters = [
            'bar' => [
                20 => [
                    '__return_empty_string' => [ 'cb' => '__return_empty_string', 'num_args' => 2 ]
                ]
            ]
        ];
        HooksMock::$hooks = [ 'actions' => $actions, 'filters' => $filters ];
        HooksMock::assertHookAdded( 'action', 'foo', '__return_true', 10, 2 );
    }

    function testAssertHookAddedNotThrow() {
        $actions = [
            'foo' => [
                10 => [
                    '__return_true' => [ 'cb' => [ '__return_true' ], 'num_args' => 1 ],
                ]
            ]
        ];
        $filters = [
            'bar' => [
                20 => [
                    '__return_empty_string' => [ 'cb' => '__return_empty_string', 'num_args' => 2 ]
                ]
            ]
        ];
        HooksMock::$hooks = [ 'actions' => $actions, 'filters' => $filters ];
        HooksMock::assertHookAdded( 'action', 'foo', '__return_true', 10, 1 );
        HooksMock::assertHookAdded( 'filter', 'bar', '__return_empty_string', 20, 2 );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testAssertHookFiredIfBadHook() {
        HooksMock::assertHookFired( 'action', TRUE );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testAssertHookFiredIfBadArgs() {
        HooksMock::assertHookFired( 'action', 'foo', 2 );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testAssertHookAddedThrowIfNWrongAction() {
        $actions = [
            'hook1' => [
                [ 'foo', [ 'foo', 'bar' ], TRUE ],
                [ TRUE ]
            ],
            'hook2' => [
                [ [ 'foo', 'bar' ] ]
            ]
        ];
        $filters = [
            'hook3' => [
                [ 'actual' ],
                [ 'actual', [ 'foo', 'bar' ] ]
            ]
        ];
        HooksMock::$hooks_done = [ 'actions' => $actions, 'filters' => $filters ];
        HooksMock::assertHookFired( 'action', 'hook3' );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testAssertHookAddedThrowIfNWrongFilter() {
        $actions = [
            'hook1' => [
                [ 'foo', [ 'foo', 'bar' ], TRUE ],
                [ TRUE ]
            ],
            'hook2' => [
                [ [ 'foo', 'bar' ] ]
            ]
        ];
        $filters = [
            'hook3' => [
                [ 'actual' ],
                [ 'actual', [ 'foo', 'bar' ] ]
            ]
        ];
        HooksMock::$hooks_done = [ 'actions' => $actions, 'filters' => $filters ];
        HooksMock::assertHookFired( 'filter', 'hook2' );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testAssertHookAddedThrowIfNWrongActionArgs() {
        $actions = [
            'hook1' => [
                [ 'foo', [ 'foo', 'bar' ], TRUE ],
                [ TRUE ]
            ],
            'hook2' => [
                [ [ 'foo', 'bar' ] ]
            ]
        ];
        $filters = [
            'hook3' => [
                [ 'actual' ],
                [ 'actual', [ 'foo', 'bar' ] ]
            ]
        ];
        HooksMock::$hooks_done = [ 'actions' => $actions, 'filters' => $filters ];
        HooksMock::assertHookFired( 'action', 'hook1', [ 'foo' ] );
    }

    /**
     * @expectedException \Brain\Striatum\Tests\HookException
     */
    function testAssertHookAddedThrowIfNWrongFilterArgs() {
        $actions = [
            'hook1' => [
                [ 'foo', [ 'foo', 'bar' ], TRUE ],
                [ TRUE ]
            ],
            'hook2' => [
                [ [ 'foo', 'bar' ] ]
            ]
        ];
        $filters = [
            'hook3' => [
                [ 'actual' ],
                [ 'actual', [ 'foo', 'bar' ] ]
            ]
        ];
        HooksMock::$hooks_done = [ 'actions' => $actions, 'filters' => $filters ];
        HooksMock::assertHookFired( 'filter', 'hook3', [ 'actual', 'foo', 'bar' ] );
    }

    function testAssertHookFiredNotThrow() {
        $actions = [
            'hook1' => [
                [ 'foo', [ 'foo', 'bar' ], TRUE ],
                [ TRUE ]
            ],
            'hook2' => [
                [ [ 'foo', 'bar' ] ]
            ]
        ];
        $filters = [
            'hook3' => [
                [ 'actual' ],
                [ 'actual', [ 'foo', 'bar' ] ]
            ]
        ];
        HooksMock::$hooks_done = [ 'actions' => $actions, 'filters' => $filters ];
        HooksMock::assertHookFired( 'action', 'hook1', [ 'foo', [ 'foo', 'bar' ], TRUE ] );
        HooksMock::assertHookFired( 'action', 'hook1', [ TRUE ] );
        HooksMock::assertHookFired( 'filter', 'hook3', [ 'actual' ] );
        HooksMock::assertHookFired( 'filter', 'hook3', [ 'actual', [ 'foo', 'bar' ] ] );
    }

}