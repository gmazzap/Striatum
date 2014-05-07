<?php namespace Brain\Striatum;

/**
 * Is the data structure used bt Subject to store Hook (Observer) instances
 *
 * @package Brain\Striatum
 */
interface BucketInterface {

    /**
     * Add an hook instance into the bucket.
     *
     * @param \Brain\Striatum\HookInterface $hook   Hook to store
     */
    function add( HookInterface $hook );

    /**
     * Remove an hook instance from the bucket.
     *
     * @param \Brain\Striatum\HookInterface $hook   Hook to remove
     */
    function remove( HookInterface $hook );

    /**
     * Retrieve a strored hook object using its id
     *
     * @param string $id    Hook id to retrieve
     * @return \Brain\Striatum\HookInterface
     */
    function get( $id );
}