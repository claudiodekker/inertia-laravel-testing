<?php

namespace ClaudioDekker\Inertia;

use Illuminate\Foundation\Testing\TestResponse as LegacyTestResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;
use Illuminate\View\FileViewFinder;
use LogicException;

class InertiaTestingServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (App::runningUnitTests()) {
            $this->registerTestingMacros();
        }
    }

    public function register()
    {
        $this->app->bind('inertia-laravel-testing.view.finder', function ($app) {
            return new FileViewFinder(
                $app['files'],
                $app['config']->get('inertia-laravel-testing.page.paths', [resource_path('js/Pages')]),
                $app['config']->get('inertia-laravel-testing.page.extensions', ['vue', 'svelte'])
            );
        });
    }

    protected function registerTestingMacros()
    {
        // Laravel >= 7.0
        if (class_exists(TestResponse::class)) {
            TestResponse::mixin(new Assertions());

            return;
        }

        // Laravel <= 6.0
        if (class_exists(LegacyTestResponse::class)) {
            LegacyTestResponse::mixin(new Assertions());

            return;
        }

        throw new LogicException('Could not detect TestResponse class.');
    }
}
