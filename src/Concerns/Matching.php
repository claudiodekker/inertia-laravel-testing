<?php

namespace ClaudioDekker\Inertia\Concerns;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;

trait Matching
{
    public function whereAll(array $bindings): self
    {
        foreach ($bindings as $key => $value) {
            $this->where($key, $value);
        }

        return $this;
    }

    public function where($key, $expected): self
    {
        $this->has($key);

        $actual = $this->prop($key);
        if (is_array($actual)) {
            array_multisort($actual);
        }

        if ($expected instanceof Closure) {
            PHPUnit::assertTrue(
                $expected(is_array($actual) ? Collection::make($actual) : $actual),
                sprintf('Inertia property [%s] was marked as invalid using a closure.', $this->dotPath($key))
            );

            return $this;
        }

        if ($expected instanceof Arrayable) {
            $expected = $expected->toArray();
        } elseif ($expected instanceof Responsable) {
            $expected = json_decode(json_encode($expected->toResponse(request())->getData()), true);
        }

        if (is_array($expected)) {
            array_multisort($expected);
        }

        PHPUnit::assertSame(
            $expected,
            $actual,
            sprintf('Inertia property [%s] does not match the expected value.', $this->dotPath($key))
        );

        return $this;
    }

    abstract protected function dotPath($key): string;

    abstract protected function prop(string $key = null);

    abstract public function has(string $key, $value = null, Closure $scope = null);
}
