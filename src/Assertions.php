<?php

namespace ClaudioDekker\Inertia;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use PHPUnit\Framework\Assert as PHPUnit;

class Assertions
{
    public const MESSAGE_NOT_A_VALID_INERTIA_RESPONSE = 'Not a valid Inertia response.';
    public const MESSAGE_UNEXPECTED_INERTIA_COMPONENT = 'Unexpected Inertia page component.';
    public const MESSAGE_INERTIA_COMPONENT_NOT_FOUND = 'Inertia page component [%s] does not exist.';

    public function assertInertia()
    {
        return function ($component = null, $props = []) {
            $this->assertViewHas('page');

            tap($this->viewData('page'), function ($view) {
                PHPUnit::assertArrayHasKey('component', $view, Assertions::MESSAGE_NOT_A_VALID_INERTIA_RESPONSE);
                PHPUnit::assertArrayHasKey('props', $view, Assertions::MESSAGE_NOT_A_VALID_INERTIA_RESPONSE);
                PHPUnit::assertArrayHasKey('url', $view, Assertions::MESSAGE_NOT_A_VALID_INERTIA_RESPONSE);
                PHPUnit::assertArrayHasKey('version', $view, Assertions::MESSAGE_NOT_A_VALID_INERTIA_RESPONSE);
            });

            if (! is_null($component)) {
                PHPUnit::assertEquals($component, $this->viewData('page')['component'], Assertions::MESSAGE_UNEXPECTED_INERTIA_COMPONENT);
            }

            if (! is_null($component) && InertiaTesting::shouldCheckForPageExistence()) {
                try {
                    app('inertia-laravel-testing.view.finder')->find($component);
                } catch (InvalidArgumentException $exception) {
                    PHPUnit::fail(sprintf(Assertions::MESSAGE_INERTIA_COMPONENT_NOT_FOUND, $component));
                }
            }

            $this->assertInertiaHasAll($props);

            return $this;
        };
    }

    public function assertInertiaHas()
    {
        return function ($key, $value = null) {
            if (is_array($key)) {
                return $this->assertInertiaHasAll($key);
            }

            if (is_null($value)) {
                PHPUnit::assertTrue(Arr::has($this->inertiaProps(), $key));
            } elseif ($value instanceof Closure) {
                PHPUnit::assertTrue($value($this->inertiaProps($key)));
            } elseif ($value instanceof Arrayable) {
                PHPUnit::assertEquals($value->toArray(), $this->inertiaProps($key));
            } elseif ($value instanceof Responsable) {
                PHPUnit::assertEquals($value->toResponse($this)->getData(), $this->inertiaProps($key));
            } else {
                PHPUnit::assertEquals($value, $this->inertiaProps($key));
            }

            return $this;
        };
    }

    public function assertInertiaHasAll()
    {
        return function (array $bindings) {
            foreach ($bindings as $key => $value) {
                if (is_int($key)) {
                    $this->assertInertiaHas($value);
                } else {
                    $this->assertInertiaHas($key, $value);
                }
            }

            return $this;
        };
    }

    public function assertInertiaMissing()
    {
        return function ($key) {
            $this->assertInertia();

            PHPUnit::assertFalse(Arr::has($this->inertiaProps(), $key));

            return $this;
        };
    }

    public function assertInertiaCount()
    {
        return function ($key, $count) {
            $this->assertInertia();

            PHPUnit::assertCount($count, Arr::get($this->inertiaProps(), $key, []));

            return $this;
        };
    }

    public function inertiaProps()
    {
        return function ($key = null) {
            $this->assertInertia();

            if (is_null($key)) {
                return $this->viewData('page')['props'];
            }

            return Arr::get($this->viewData('page')['props'], $key);
        };
    }
}
