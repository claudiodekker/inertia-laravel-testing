<?php

namespace ClaudioDekker\Inertia;

use Illuminate\Foundation\Testing\TestResponse as LegacyTestResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;
use Illuminate\View\FileViewFinder;
use LogicException;
use Inertia\Assert as InertiaAssertions;

class InertiaTestingServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // When the installed Inertia adapter has our assertions bundled,
        // we'll skip registering and/or booting this package.
        if (class_exists(InertiaAssertions::class)) {
            return;
        }

        if (App::runningUnitTests()) {
            $this->registerTestingMacros();
        }

        $this->publishes([
            __DIR__.'/../config/inertia.php' => config_path('inertia.php'),
        ]);
    }

    public function register()
    {
        // When the installed Inertia adapter has our assertions bundled,
        // we'll skip registering and/or booting this package.
        if (class_exists(InertiaAssertions::class)) {
            return;
        }

        $this->mergeConfigFrom(
            __DIR__.'/../config/inertia.php',
            'inertia'
        );

        $this->app->bind('inertia.view.finder', function ($app) {
            return new FileViewFinder(
                $app['files'],
                $app['config']->get('inertia.page.paths'),
                $app['config']->get('inertia.page.extensions')
            );
        });
    }

    protected function registerTestingMacros()
    {
        // Laravel >= 7.0
        if (class_exists(TestResponse::class)) {
            TestResponse::mixin(new TestResponseMacros());

            return;
        }

        // Laravel <= 6.0
        if (class_exists(LegacyTestResponse::class)) {
            LegacyTestResponse::mixin(new TestResponseMacros());

            return;
        }

        throw new LogicException('Could not detect TestResponse class.');
    }
}
