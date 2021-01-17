<?php

namespace ClaudioDekker\Inertia\Tests;

use ClaudioDekker\Inertia\InertiaTestingServiceProvider;
use Illuminate\Foundation\Testing\TestResponse as LegacyTestResponse;
use Illuminate\Testing\TestResponse;
use Inertia\Inertia;
use LogicException;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Inertia::setRootView('welcome');
        config()->set('inertia-testing.page.should_exist', false);
        config()->set('inertia-testing.page.paths', [realpath(__DIR__)]);
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

    /**
     * @return string
     * @throws LogicException
     */
    protected function getTestResponseClass(): string
    {
        // Laravel >= 7.0
        if (class_exists(TestResponse::class)) {
            return TestResponse::class;
        }

        // Laravel <= 6.0
        if (class_exists(LegacyTestResponse::class)) {
            return LegacyTestResponse::class;
        }

        throw new LogicException('Could not detect TestResponse class.');
    }

    protected function makeMockRequest($view)
    {
        app('router')->get('/example-url', function () use ($view) {
            return $view;
        });

        return $this->get('/example-url');
    }
}
