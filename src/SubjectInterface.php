<?php

namespace Brain\Striatum;

interface SubjectInterface {

    public function setId( $id );

    public function getId();

    public function add( HookInterface $hook );

    public function remove( HookInterface $hook );

    public function setHooks( BucketInterface $booket );

    public function getHooks();

    public function getHook( $id );

    public function setArgs( Array $args );

    public function getArgs();

    public function isFilter( $set = FALSE );

    public function detachAll();

    public function restoreAll();

    public function removeAll();
}