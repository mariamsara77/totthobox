<?php

namespace Tests;

use App\Http\Middleware\TrackVisitors;
use App\Http\Middleware\UpdateLastActive;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            TrackVisitors::class,
            UpdateLastActive::class,
        ]);
    }
}
