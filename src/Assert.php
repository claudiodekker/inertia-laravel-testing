<?php

namespace ClaudioDekker\Inertia;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
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

    protected function dotPath($key): string
    {
        if (is_null($this->path)) {
            return $key;
        }

        return implode(".", [$this->path, $key]);
    }

    protected function __construct(string $component, array $props, string $path = null)
    {
        $this->path = $path;
        $this->component = $component;
        $this->props = $props;
    }

    protected function scope($key): self
    {
        $prop = $this->prop($key);
        $path = $this->dotPath($key);

        PHPUnit::assertIsArray($prop, sprintf("Inertia property [%s] is not scopeable.", $path));

        return new self(
            $this->component,
            $prop,
            $path
        );
    }

    public static function fromTestResponse(TestResponse $response) : self
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

    protected function prop(string $key = null)
    {
        return Arr::get($this->props, $key);
    }

    public function has($key, Closure $callback = null): self
    {
        PHPUnit::assertTrue(
            Arr::has($this->prop(), $key),
            sprintf("Inertia property [%s] does not exist.", $this->dotPath($key))
        );

        if (! is_null($callback)) {
            $callback($this->scope($key));
        }

        return $this;
    }

    public function where($key, $value): self
    {
        $this->has($key);

        PHPUnit::assertEquals(
            $value,
            $this->prop($key),
            sprintf('Inertia property [%s] does not match the expected value.', $this->dotPath($key))
        );

        return $this;
    }
}
