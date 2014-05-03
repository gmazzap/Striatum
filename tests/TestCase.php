<?php

namespace Brain\Striatum\Tests;

class TestCase extends \PHPUnit_Framework_TestCase {

    public function tearDown() {
        parent::tearDown();
        HooksMock::tearDown();
    }

}