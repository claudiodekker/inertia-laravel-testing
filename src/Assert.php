<?php

namespace ClaudioDekker\Inertia;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
    private $url;

    /** @var mixed|null */
    private $version;

    /** @var string */
    private $path;

    /** @var array */
    protected $interacted = [];

    protected function __construct(string $component, array $props, string $url, $version = null, string $path = null)
    {
        $this->path = $path;

        $this->component = $component;
        $this->props = $props;
        $this->url = $url;
        $this->version = $version;
    }

    protected function interactsWith(string $key): void
    {
        $prop = Str::before($key, '.');

        if (! in_array($prop, $this->interacted, true)) {
            $this->interacted[] = $prop;
        }
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

    protected function scope($key, Closure $callback): self
    {
        $props = $this->prop($key);
        $path = $this->dotPath($key);

        PHPUnit::assertIsArray($props, sprintf('Inertia property [%s] is not scopeable.', $path));

        $scope = new self($this->component, $props, $this->url, $this->version, $path);
        $callback($scope);
        $scope->interacted();

        return $this;
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

        return new self($page['component'], $page['props'], $page['url'], $page['version']);
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

    public function etc(): self
    {
        $this->interacted = array_keys($this->prop());

        return $this;
    }

    public function component(string $value = null, $shouldExist = null): self
    {
        PHPUnit::assertSame($value, $this->component, 'Unexpected Inertia page component.');

        if ($shouldExist || (is_null($shouldExist) && config('inertia.page.should_exist', true))) {
            try {
                app('inertia.view.finder')->find($value);
            } catch (InvalidArgumentException $exception) {
                PHPUnit::fail(sprintf('Inertia page component file [%s] does not exist.', $value));
            }
        }

        return $this;
    }

    public function url(string $value): self
    {
        PHPUnit::assertSame($value, $this->url, 'Unexpected Inertia page url.');

        return $this;
    }

    public function version($value): self
    {
        PHPUnit::assertSame($value, $this->version, 'Unexpected Inertia asset version.');

        return $this;
    }

    public function hasAll($key): self
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $prop => $count) {
            if (is_int($prop)) {
                $this->has($count);
            } else {
                $this->has($prop, $count);
            }
        }

        return $this;
    }

    public function has(string $key, $value = null, Closure $scope = null): self
    {
        PHPUnit::assertTrue(
            Arr::has($this->prop(), $key),
            sprintf('Inertia property [%s] does not exist.', $this->dotPath($key))
        );

        $this->interactsWith($key);

        if (is_int($value) && ! is_null($scope)) {
            $path = $this->dotPath($key);

            $prop = $this->prop($key);
            if ($prop instanceof Collection) {
                $prop = $prop->all();
            }

            PHPUnit::assertTrue($value > 0, sprintf('Cannot scope directly onto the first entry of property [%s] when asserting that it has a size of 0.', $path));
            PHPUnit::assertIsArray($prop, sprintf('Direct scoping is currently unsupported for non-array like properties such as [%s].', $path));
            $this->count($key, $value);

            return $this->scope($key.'.'.array_keys($prop)[0], $scope);
        }

        if (is_int($value)) {
            return $this->count($key, $value);
        }

        if (is_callable($value)) {
            $this->scope($key, $value);
        }

        return $this;
    }

    public function missesAll($key): self
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $prop) {
            $this->misses($prop);
        }

        return $this;
    }

    public function misses(string $key): self
    {
        $this->interactsWith($key);

        PHPUnit::assertNotTrue(
            Arr::has($this->prop(), $key),
            sprintf('Inertia property [%s] was found while it was expected to be missing.', $this->dotPath($key))
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
