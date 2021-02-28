<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPUnit\Framework\Assert;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Database\Eloquent\Collection::macro('assertTheSame', function ($arr = []) {

            Assert::assertEquals(count($arr), $this->count());
            $zipped = $this->zip($arr);

            $zipped->each(function ($pair) {
                list($left, $right) = $pair;
                Assert::assertTrue($left->is($right));
            });
        });
    }
}
