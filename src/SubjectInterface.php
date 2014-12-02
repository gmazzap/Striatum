<?php namespace Brain\Striatum;

/**
 * Subject is one of the two actors in the observer pattern. This package make use of a Subject for
 * any hook tag added.
 *
 * @package Brain\Striatum
 */
interface SubjectInterface
{

    /**
     * Set an id for the Subject. It coincides with the action/filter tag, e.g. 'init' or 'the_content'
     *
     * @param string $id
     */
    public function setId($id);

    /**
     * Get the id
     *
     * @return string
     */
    public function getId();

    /**
     * Attach an Hook (observer) to the Subject
     *
     * @param \Brain\Striatum\HookInterface $hook   Hook to add
     */
    public function add(HookInterface $hook);

    /**
     * Detach an Hook (observer) to the Subject
     *
     * @param \Brain\Striatum\HookInterface $hook   Hook to remove
     */
    public function remove(HookInterface $hook);

    /**
     * BucketInterface is the data structure where the added Hooks are stored.
     *
     * @param \Brain\Striatum\BucketInterface $booket
     */
    public function setHooks(BucketInterface $booket);

    /**
     * Get the BucketInterface
     *
     * @return \Brain\Striatum\BucketInterface
     */
    public function getHooks();

    /**
     * Get a specific hook from the bucket via its id
     *
     * @param string $id    Hook to retrieve
     * @return \Brain\Striatum\HookInterface
     */
    public function getHook($id);

    /**
     * Both a getter/setter for the 'is_filter' property. When $set paran is not null, acts as a
     * setter, otherwise as a getter.
     *
     * @param bool|void $set    When not null 'is_filter' is set to this value casted to boolean
     * @return bool
     */
    public function isFilter($set = NULL);

    /**
     * Get a specific information about the Subject. E.g. the hooks running, the ones already ran,
     * current priority, and so on.
     *
     * @param string $info  The info to get
     */
    public function getInfo($info = NULL);

    /**
     * Set a specific information about the Subject. E.g. the hooks running, the ones already ran,
     * current priority, and so on.
     *
     * @param string $info  The info to set
     * @param mixed $value  Valur to assign to the info
     */
    public function setInfo($info = NULL, $value = NULL);

    /**
     * Detach all Hooks attached, so remove them from the bucket and from global $wp_filter array
     */
    public function detachAll();

    /**
     * Restore hooks from bucket to WordPress global $wp_filter array
     */
    public function restoreAll();

    /**
     * Remove hooks from WordPress global $wp_filter array but keep them in the Bucket
     */
    public function removeAll();
}