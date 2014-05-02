<?php

namespace Brain\Striatum;

interface BucketInterface {

    function add( HookInterface $hook );

    function remove( HookInterface $hook );

    function get( $id );
}