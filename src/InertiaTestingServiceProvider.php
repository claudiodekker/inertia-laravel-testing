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

        $this->publishes([
            __DIR__.'/../config/inertia-testing.php' => config_path('inertia-testing.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/inertia-testing.php',
            'inertia-testing'
        );

        $this->app->bind('inertia-testing.view.finder', function ($app) {
            return new FileViewFinder(
                $app['files'],
                $app['config']->get('inertia-testing.page.paths'),
                $app['config']->get('inertia-testing.page.extensions')
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
