<?php namespace Brain\Striatum\Tests\Unit;

use Brain\Striatum\Tests\TestCase;

class ContextableTest extends TestCase {

    private function get() {
        return new \Brain\Striatum\Tests\ContextStub;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testSetContextFailsIfNullIndexNotNullValue() {
        $context = $this->get();
        $context->setContext( 'context', NULL, 'foo' );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testSetContextFailsIfBadIndex() {
        $context = $this->get();
        $context->setContext( 'context', TRUE, 'foo' );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testSetContextFailsIfBadKey() {
        $context = $this->get();
        $context->setContext( TRUE, 'foo', 'foo' );
    }

    function testSetContextReset() {
        $context = $this->get();
        $context->context['foo'] = 'bar';
        assertTrue( $context->context->offsetExists( 'foo' ) );
        $context->resetContext( 'context' );
        assertFalse( $context->context->offsetExists( 'foo' ) );
    }

    function testSetContext() {
        $context = $this->get();
        $a = $context->setContext( 'context', 'foo', 'bar' );
        $b = $context->setContext( 'context', 'bar', 'baz' );
        $c = $context->setContext( 'context', 'baz' );
        assertEquals( 'bar', $context->context['foo'] );
        assertEquals( 'baz', $context->context['bar'] );
        assertNull( $context->context['baz'] );
        assertEquals( $context, $a );
        assertEquals( $context, $b );
        assertEquals( $context, $c );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testGetContextFailsIfBadIndex() {
        $context = $this->get();
        $context->getContext( 'context', TRUE );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testGetContextFailsIfBadKey() {
        $context = $this->get();
        $context->getContext( 'foo', 'foo' );
    }

    function testGetContextGetFullArray() {
        $context = $this->get();
        $context->context['foo'] = 'bar';
        $context->context['bar'] = 'baz';
        $context->context['baz'] = NULL;
        $expected = [ 'foo' => 'bar', 'bar' => 'baz', 'baz' => NULL ];
        assertEquals( $expected, $context->getContext( 'context' ) );
    }

    function testGetContext() {
        $context = $this->get();
        $context->context['foo'] = 'bar';
        $context->context['bar'] = 'baz';
        $context->context['baz'] = NULL;
        assertEquals( 'bar', $context->getContext( 'context', 'foo' ) );
        assertEquals( 'baz', $context->getContext( 'context', 'bar' ) );
        assertNull( $context->getContext( 'context', 'baz' ) );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testUnsetContextFailsIfBadKey() {
        $context = $this->get();
        $context->context['foo'] = 'bar';
        $context->unsetContext( 'foo', 'foo' );
    }

    function testUnsetContextReset() {
        $context = $this->get();
        $context->context['foo'] = 'bar';
        $context->context['bar'] = 'baz';
        $context->unsetContext( 'context' );
        assertEquals( [ ], $context->getContext( 'context' ) );
    }

    function testUnsetContext() {
        $context = $this->get();
        $context->context['foo'] = 'bar';
        $context->context['bar'] = 'baz';
        $a = $context->unsetContext( 'context', 'foo' );
        assertEquals( [ 'bar' => 'baz' ], $context->getContext( 'context' ) );
        assertEquals( $context, $a );
    }

}