<?php

namespace ClaudioDekker\Inertia;

use Illuminate\Foundation\Testing\TestResponse as LegacyTestResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;
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
        //
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
