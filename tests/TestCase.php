<?php

namespace ClaudioDekker\Inertia\Tests;

use ClaudioDekker\Inertia\InertiaTestingServiceProvider;
use Inertia\Inertia;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Inertia::setRootView('welcome');
        config()->set('inertia-testing.page.should_exist', false);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            InertiaTestingServiceProvider::class,
        ];
    }
}
