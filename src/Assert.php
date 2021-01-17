<?php

namespace ClaudioDekker\Inertia;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\AssertionFailedError;

class Assert
{
    /** @var string */
    private $component;

    /** @var array */
    private $props;

    /** @var string */
    private $path;

    /** @var array */
    protected $interacted = [];

    protected function __construct(string $component, array $props, string $path = null)
    {
        $this->path = $path;
        $this->component = $component;
        $this->props = $props;
    }

    public function interacted(): void
    {
        PHPUnit::assertSame(
            [],
            array_diff(array_keys($this->prop()), $this->interacted),
            $this->path
                ? sprintf('Unexpected Inertia properties were found in scope [%s].', $this->path)
                : 'Unexpected Inertia properties were found on the root level.'
        );
    }

    protected function interactsWith(string $key): void
    {
        $prop = Str::before($key, '.');

        if (! in_array($prop, $this->interacted, true)) {
            $this->interacted[] = $prop;
        }
    }

    public function etc(): self
    {
        $this->interacted = array_keys($this->prop());

        return $this;
    }

    protected function dotPath($key): string
    {
        if (is_null($this->path)) {
            return $key;
        }

        return implode('.', [$this->path, $key]);
    }

    protected function prop(string $key = null)
    {
        return Arr::get($this->props, $key);
    }

    protected function count(string $key, $length): self
    {
        PHPUnit::assertCount(
            $length,
            $this->prop($key),
            sprintf('Inertia property [%s] does not have the expected size.', $this->dotPath($key))
        );

        return $this;
    }

    protected function scope($key): self
    {
        $prop = $this->prop($key);
        $path = $this->dotPath($key);

        PHPUnit::assertIsArray($prop, sprintf('Inertia property [%s] is not scopeable.', $path));

        return new self(
            $this->component,
            $prop,
            $path
        );
    }

    public static function fromTestResponse($response): self
    {
        try {
            $response->assertViewHas('page');
            $page = $response->viewData('page');

            PHPUnit::assertIsArray($page);
            PHPUnit::assertArrayHasKey('component', $page);
            PHPUnit::assertArrayHasKey('props', $page);
            PHPUnit::assertArrayHasKey('url', $page);
            PHPUnit::assertArrayHasKey('version', $page);
        } catch (AssertionFailedError $e) {
            PHPUnit::fail('Not a valid Inertia response.');
        }

        return new self($page['component'], $page['props']);
    }

    public function component(string $component = null, $shouldExist = false): self
    {
        PHPUnit::assertSame($component, $this->component, 'Unexpected Inertia page component.');

        if ($shouldExist || config('inertia-testing.page.should_exist', true)) {
            try {
                app('inertia-testing.view.finder')->find($component);
            } catch (InvalidArgumentException $exception) {
                PHPUnit::fail(sprintf('Inertia page component file [%s] does not exist.', $component));
            }
        }

        return $this;
    }

    public function hasAll(array $bindings): self
    {
        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $this->has($value);
            } else {
                $this->has($key, $value);
            }
        }

        return $this;
    }

    public function has($key, $value = null): self
    {
        if (! is_string($key)) {
            return $this->hasAll($key);
        }

        PHPUnit::assertTrue(
            Arr::has($this->prop(), $key),
            sprintf('Inertia property [%s] does not exist.', $this->dotPath($key))
        );

        $this->interactsWith($key);

        if (is_int($value)) {
            return $this->count($key, $value);
        }

        if (is_callable($value)) {
            $scope = $this->scope($key);

            $value($scope);

            $scope->interacted();
        }

        return $this;
    }

    public function misses($key): self
    {
        $this->interactsWith($key);

        PHPUnit::assertNotTrue(
            Arr::has($this->prop(), $key),
            sprintf('Inertia property [%s] exists when it was not expected to.', $this->dotPath($key))
        );

        return $this;
    }

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
            PHPUnit::assertTrue(
                $value($this->prop($key)),
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
                $value->toResponse(request())->getData(),
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
}
