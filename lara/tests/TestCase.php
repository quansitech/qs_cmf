<?php
namespace Lara\Tests;

use Testing\TestCase as BaseTestCase;

class TestCase extends BaseTestCase{

    public function laraPath():string{
        return __DIR__ . '/..';
    }
}