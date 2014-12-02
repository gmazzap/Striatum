<?php namespace Brain\Striatum;

/**
 * Hook are the Observer actor in the Observer pattern. Hooks are defined mainly by an id, and a
 * callback, and by a number of additional properties.
 *
 * @package Brain\Striatum
 */
interface HookInterface
{

    /**
     * Set the id of the hook instance.
     *
     * @param string $id    Set hook id
     */
    public function setId($id);

    /**
     * Get the id of the hook.
     *
     * @return string
     */
    function getId();

    /**
     * Hook is the Observer actor in the Observer pattern, related Subject is set via this method.
     *
     * @param \Brain\Striatum\SubjectInterface $subject Hook Subject
     */
    function setSubject(SubjectInterface $subject);

    /**
     * Get the hook Subject
     *
     * @return  \Brain\Striatum\SubjectInterface
     */
    function getSubject();

    /**
     * Get an additional property of the hook, like callback, priority, and so on
     *
     * @param string $index The property to get
     */
    function get($index = NULL);

    /**
     * Set an additional property of the hook, like callback, priority, and so on
     *
     * @param string $index The property to set
     * @param mixed $value  Value to assign to property
     */
    function set($index = NULL, $value = NULL);

    /**
     * Before being added, this method validate and sanitize an array of arguments to be set as
     * Hook property
     *
     * @param array $args   Hook argument
     */
    function prepare($args);

    /**
     * Before being added and again being fired, this method check if current argumenst are valid,
     * e.g. if the callback is a valid callable and so on.
     *
     * @return boolean
     */
    function check();

    /**
     * This method is the only one added to WordPress global $wp_filter array, so is the one always
     * called by do_action and apply_filter, and is responsible to call the callback associated to
     * hook instance.
     */
    function proxy();
}