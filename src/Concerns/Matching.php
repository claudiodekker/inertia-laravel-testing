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

    public function where($key, $value): self
    {
        $this->has($key);

        if ($value instanceof Closure) {
            $prop = $this->prop($key);

            PHPUnit::assertTrue(
                $value(is_array($prop) ? Collection::make($prop) : $prop),
                sprintf('Inertia property [%s] was marked as invalid using a closure.', $this->dotPath($key))
            );
        } elseif ($value instanceof Arrayable) {
            PHPUnit::assertEquals(
                $value->toArray(),
                $this->prop($key),
                sprintf('Inertia property [%s] does not match the expected Arrayable.', $this->dotPath($key))
            );
        } elseif ($value instanceof Responsable) {
            PHPUnit::assertEquals(
                json_decode(json_encode($value->toResponse(request())->getData()), true),
                $this->prop($key),
                sprintf('Inertia property [%s] does not match the expected Responsable.', $this->dotPath($key))
            );
        } else {
            PHPUnit::assertEquals(
                $value,
                $this->prop($key),
                sprintf('Inertia property [%s] does not match the expected value.', $this->dotPath($key))
            );
        }

        return $this;
    }

    abstract protected function dotPath($key): string;

    abstract protected function prop(string $key = null);

    abstract public function has(string $key, $value = null, Closure $scope = null);
}
