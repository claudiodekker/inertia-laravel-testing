<?php

namespace ClaudioDekker\Inertia\Tests\Unit;

use ClaudioDekker\Inertia\Assert;
use ClaudioDekker\Inertia\Tests\TestCase;
use Inertia\Inertia;
use PHPUnit\Framework\AssertionFailedError;

class AssertTest extends TestCase
{
    /** @test */
    public function the_view_is_served_by_inertia(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        $response->assertInertia();
    }

    /** @test */
    public function the_view_is_not_served_by_inertia(): void
    {
        $response = $this->makeMockRequest(view('welcome'));
        $response->assertOk(); // Make sure we can render the built-in Orchestra 'welcome' view..

        $this->expectException(AssertionFailedError::class);

        $response->assertInertia();
    }

    /** @test */
    public function it_preserves_the_ability_to_continue_chaining_test_response_calls(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        $this->assertInstanceOf(
            $this->getTestResponseClass(),
            $response->assertInertia()
        );
    }

    /** @test */
    public function the_inertia_component_matches(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('foo');
        });
    }

    /** @test */
    public function the_inertia_component_does_not_match(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('bar');
        });
    }

    /** @test */
    public function the_inertia_component_exists_on_the_filesystem(): void
    {
        config()->set('inertia-testing.page.should_exist', true);

        $response = $this->makeMockRequest(
            Inertia::render('fixtures/ExamplePage')
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('fixtures/ExamplePage');
        });
    }

    /** @test */
    public function the_inertia_component_does_not_exist_on_the_filesystem(): void
    {
        config()->set('inertia-testing.page.should_exist', true);
        $this->expectException(AssertionFailedError::class);

        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('foo');
        });
    }

    /** @test */
    public function it_can_enforce_the_component_file_existence_check_when_the_setting_is_globally_disabled(): void
    {
        config()->set('inertia-testing.page.should_exist', false);
        $this->expectException(AssertionFailedError::class);

        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('foo', true);
        });
    }

    /** @test */
    public function the_inertia_component_does_not_exist_on_the_filesystem_when_it_does_not_exist_relative_to_any_of_the_given_paths(): void
    {
        config()->set('inertia-testing.page.should_exist', true);
        config()->set('inertia-testing.page.paths', [realpath(__DIR__)]);
        $this->expectException(AssertionFailedError::class);

        $response = $this->makeMockRequest(
            Inertia::render('fixtures/ExamplePage')
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('fixtures/ExamplePage');
        });
    }

    /** @test */
    public function the_inertia_component_does_not_exist_on_the_filesystem_when_it_does_not_have_one_of_the_configured_extensions(): void
    {
        config()->set('inertia-testing.page.should_exist', true);
        config()->set('inertia-testing.page.extensions', ['bin', 'exe', 'svg']);
        $this->expectException(AssertionFailedError::class);

        $response = $this->makeMockRequest(
            Inertia::render('fixtures/ExamplePage')
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('fixtures/ExamplePage');
        });
    }

    /** @test */
    public function it_has_the_inertia_prop(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'prop' => 'value'
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('prop');
        });
    }

    /** @test */
    public function it_does_not_have_the_inertia_prop(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => 'value'
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('prop');
        });
    }
}
