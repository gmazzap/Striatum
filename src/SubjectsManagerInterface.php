<?php namespace Brain\Striatum;

/**
 * The class that manage the Subject instance.
 *
 * Subject is one of the two actors in the observer pattern. This package make use of a Subject for
 * any hook tag added.
 *
 * @package Brain\Striatum
 */
interface SubjectsManagerInterface {

    /**
     * Get an existent subject instance by its id.
     *
     * @param string $id    Subject ID
     * @return Brain\Striatum\SubjectInterface
     */
    function getSubject( $id );

    /**
     * Get an existent frozen subject instance by its id
     *
     * @param string $id    Frozen Subject ID
     * @return Brain\Striatum\SubjectInterface
     */
    function getFrozenSubject( $id );

    /**
     * Add a Subject instance. Can be a filter or an action according to $is_filter param.
     *
     * @param string $id        New Subject Id
     * @param bool $is_filter   If true the Subject will be a filter
     */
    function addSubject( $id, $is_filter = FALSE );

    /**
     * Remove an existent subject instance by its id.
     *
     * @param string $id    Subject Id to remove
     */
    function removeSubject( $id );

    /**
     * Set a Subject in a frozen status.
     *
     * @param string $id    Subject Id to freeze.
     */
    function freezeSubject( $id );

    /**
     * Bring back a frozen subject to normal status.
     *
     * @param string $id    Subject Id to unfreeze.
     */
    function unfreezeSubject( $id );
}