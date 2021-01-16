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
        $this->expectExceptionMessage('Not a valid Inertia response.');

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
    public function the_component_matches(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('foo');
        });
    }

    /** @test */
    public function the_component_does_not_match(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Unexpected Inertia page component.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('bar');
        });
    }

    /** @test */
    public function the_component_exists_on_the_filesystem(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('fixtures/ExamplePage')
        );

        config()->set('inertia-testing.page.should_exist', true);
        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('fixtures/ExamplePage');
        });
    }

    /** @test */
    public function the_component_does_not_exist_on_the_filesystem(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        config()->set('inertia-testing.page.should_exist', true);
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia page component file [foo] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('foo');
        });
    }

    /** @test */
    public function it_can_enforce_the_component_file_existence_check_when_the_setting_is_globally_disabled(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        config()->set('inertia-testing.page.should_exist', false);
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia page component file [foo] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('foo', true);
        });
    }

    /** @test */
    public function the_component_does_not_exist_on_the_filesystem_when_it_does_not_exist_relative_to_any_of_the_given_paths(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('fixtures/ExamplePage')
        );

        config()->set('inertia-testing.page.should_exist', true);
        config()->set('inertia-testing.page.paths', [realpath(__DIR__)]);
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia page component file [fixtures/ExamplePage] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('fixtures/ExamplePage');
        });
    }

    /** @test */
    public function the_component_does_not_exist_on_the_filesystem_when_it_does_not_have_one_of_the_configured_extensions(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('fixtures/ExamplePage')
        );

        config()->set('inertia-testing.page.should_exist', true);
        config()->set('inertia-testing.page.extensions', ['bin', 'exe', 'svg']);
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia page component file [fixtures/ExamplePage] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('fixtures/ExamplePage');
        });
    }

    /** @test */
    public function the_prop_exists(): void
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
    public function the_prop_does_not_exist(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => 'value'
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [prop] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('prop');
        });
    }

    /** @test */
    public function the_prop_matches_a_value(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => 'value',
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->where('bar', 'value');
        });
    }

    /** @test */
    public function the_prop_does_not_match_a_value(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => 'value',
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [bar] does not match the expected value.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->where('bar', 'invalid');
        });
    }

    /** @test */
    public function the_prop_does_not_match_a_value_when_it_does_not_exist(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => 'value',
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [baz] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->where('baz', null);
        });
    }

    /** @test */
    public function it_can_scope_the_assertion_query(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => [
                    'baz' => 'example',
                    'prop' => 'value',
                ]
            ])
        );

        $called = false;
        $response->assertInertia(function (Assert $inertia) use (&$called) {
            $inertia->has('bar', function (Assert $inertia) use (&$called) {
                $called = true;
                $inertia->where('baz', 'example');
            });
        });

        $this->assertTrue($called, 'The scoped query was never actually called.');
    }

    /** @test */
    public function it_cannot_scope_the_assertion_query_when_the_scoped_prop_does_not_exist(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => [
                    'baz' => 'example',
                    'prop' => 'value',
                ]
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [baz] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('baz', function (Assert $inertia) {
                $inertia->where('baz', 'example');
            });
        });
    }

    /** @test */
    public function it_cannot_scope_the_assertion_query_when_the_scoped_prop_is_a_single_value(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => 'value'
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [bar] is not scopeable.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('bar', function (Assert $inertia) {
                //
            });
        });
    }
}
