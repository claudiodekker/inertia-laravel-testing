<?php

namespace ClaudioDekker\Inertia;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use PHPUnit\Framework\Assert as PHPUnit;

class Assert
{
    /** @var string */
    private $component;

    /** @var array */
    private $props;

    protected function __construct(string $component, array $props)
    {
        $this->component = $component;
        $this->props = $props;
    }

    public static function fromTestResponse($response) : self
    {
        $response->assertViewHas('page');

        $page = $response->viewData('page');

        PHPUnit::assertArrayHasKey('component', $page);
        PHPUnit::assertArrayHasKey('props', $page);
        PHPUnit::assertArrayHasKey('url', $page);
        PHPUnit::assertArrayHasKey('version', $page);

        return new self($page['component'], $page['props']);
    }

    public function component(string $component = null, $shouldExist = false): self
    {
        PHPUnit::assertSame($component, $this->component);

        if ($shouldExist || config('inertia-testing.page.should_exist', true)) {
            try {
                app('inertia-testing.view.finder')->find($component);
            } catch (InvalidArgumentException $exception) {
                PHPUnit::fail(sprintf('Inertia page component file [%s] does not exist.', $component));
            }
        }

        return $this;
    }

    public function has($key): self
    {
        PHPUnit::assertTrue(Arr::has($this->props, $key));

        return $this;
    }
}
