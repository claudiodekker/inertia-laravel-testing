<?php

namespace ClaudioDekker\Inertia\Tests\Unit;

use ClaudioDekker\Inertia\InertiaTesting;
use ClaudioDekker\Inertia\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use PHPUnit\Framework\AssertionFailedError;

class AssertionsTest extends TestCase
{
    public function test_the_view_is_served_by_inertia()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test')
        );

        $response->assertInertia();
    }

    public function test_the_view_is_not_served_by_inertia()
    {
        $response = $this->makeMockResponse(view('welcome'));
        $response->assertOk(); // Make sure we can render the built-in Orchestra 'welcome' view..

        $this->expectException(AssertionFailedError::class);

        $response->assertInertia();
    }

    public function test_the_inertia_component_matches()
    {
        config()->set('inertia-testing.page.should_exist', false);

        $response = $this->makeMockResponse(
            Inertia::render('test-component')
        );

        $response->assertInertia('test-component');
    }

    public function test_the_inertia_component_does_not_exist_on_the_filesystem()
    {
        $this->expectException(AssertionFailedError::class);

        $response = $this->makeMockResponse(
            Inertia::render('test-component')
        );

        $response->assertInertia('test-component');
    }

    public function test_the_inertia_component_exists_on_the_filesystem()
    {
        config()->set('inertia-testing.page.paths', [realpath(__DIR__ . '/..')]);

        $response = $this->makeMockResponse(
            Inertia::render('fixtures/ExamplePage')
        );

        $response->assertInertia('fixtures/ExamplePage');
    }

    public function test_the_inertia_component_does_not_match()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component')
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertia('another-component');
    }

    public function test_the_inertia_component_and_props_match()
    {
        config()->set('inertia-testing.page.should_exist', false);

        $response = $this->makeMockResponse(
            Inertia::render('test-component', $props = [
                'foo' => 'bar',
            ])
        );

        $response->assertInertia('test-component', $props);
    }

    public function test_the_inertia_component_and_props_do_not_match()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', $props = [
                'foo' => 'bar',
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertia('test-component', [
            'foo' => 'baz',
        ]);
    }

    public function test_the_inertia_page_has_a_prop()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example-prop' => 'example-value',
            ])
        );

        $response->assertInertiaHas('example-prop');
    }

    public function test_the_inertia_page_does_not_have_a_prop()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example-prop' => 'example-value',
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaHas('another-prop');
    }

    public function test_the_inertia_page_has_a_nested_prop()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example' => [
                    'nested' => 'nested-value',
                ],
            ])
        );

        $response->assertInertiaHas('example.nested');
    }

    public function test_the_inertia_page_does_not_have_a_nested_prop()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example' => [
                    'nested' => 'nested-value',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaHas('example.another');
    }

    public function test_the_inertia_page_has_a_prop_that_matches_a_value()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example-prop' => 'example-value',
            ])
        );

        $response->assertInertiaHas('example-prop', 'example-value');
    }

    public function test_the_inertia_page_has_a_prop_that_does_not_match_a_value()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example-prop' => 'example-value',
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaHas('example-prop', 'anohter-value');
    }

    public function test_the_inertia_page_has_a_nested_prop_that_matches_a_value()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example' => [
                    'nested' => 'nested-value',
                ],
            ])
        );

        $response->assertInertiaHas('example.nested', 'nested-value');
    }

    public function test_the_inertia_page_has_a_nested_prop_that_does_not_match_a_value()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example' => [
                    'nested' => 'nested-value',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaHas('example.nested', 'another-value');
    }

    public function test_the_inertia_page_has_a_prop_with_a_value_using_a_closure()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example-prop' => 'example-value',
            ])
        );

        $response->assertInertiaHas('example-prop', function ($value) {
            return $value === 'example-value';
        });
    }

    public function test_the_inertia_page_does_not_have_a_prop_with_a_value_using_a_closure()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example-prop' => 'example-value',
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaHas('example-prop', function ($value) {
            return $value === 'another-value';
        });
    }

    public function test_the_inertia_page_has_a_nested_prop_with_a_value_using_a_closure()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example' => [
                    'nested' => 'example-value',
                ],
            ])
        );

        $response->assertInertiaHas('example.nested', function ($value) {
            return $value === 'example-value';
        });
    }

    public function test_the_inertia_page_does_not_have_a_nested_prop_with_a_value_using_a_closure()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example' => [
                    'nested' => 'example-value',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaHas('example.nested', function ($value) {
            return $value === 'another-value';
        });
    }

    public function test_the_inertia_page_has_a_prop_with_a_value_using_an_arrayable()
    {
        Model::unguard();
        $user = User::make(['name' => 'Example']);

        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example-prop' => $user,
            ])
        );

        $response->assertInertiaHas('example-prop', $user);
    }

    public function test_the_inertia_page_does_not_have_a_prop_with_a_value_using_an_arrayable()
    {
        Model::unguard();
        $userA = User::make(['name' => 'Example']);
        $userB = User::make(['name' => 'Another']);

        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example-prop' => $userA,
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaHas('example-prop', $userB);
    }

    public function test_the_inertia_page_has_a_nested_prop_with_a_value_using_an_arrayable()
    {
        Model::unguard();
        $user = User::make(['name' => 'Example']);

        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example' => [
                    'nested' => $user,
                ],
            ])
        );

        $response->assertInertiaHas('example.nested', $user);
    }

    public function test_the_inertia_page_does_not_have_a_nested_prop_with_a_value_using_an_arrayable()
    {
        Model::unguard();
        $userA = User::make(['name' => 'Example']);
        $userB = User::make(['name' => 'Another']);

        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example' => [
                    'nested' => $userA,
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaHas('example.nested', $userB);
    }

    public function test_the_inertia_page_has_a_prop_with_a_value_using_a_responsable()
    {
        Model::unguard();
        $user = User::make(['name' => 'Example']);
        $resource = JsonResource::collection(new Collection([$user, $user]));

        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example' => $resource,
            ])
        );

        $response->assertInertiaHas('example', $resource);
    }

    public function test_the_inertia_page_does_not_have_a_prop_with_a_value_using_a_responsable()
    {
        Model::unguard();
        $resourceA = JsonResource::make(User::make(['name' => 'Another']));
        $resourceB = JsonResource::make(User::make(['name' => 'Example']));

        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example' => $resourceA,
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaHas('example', $resourceB);
    }

    public function test_the_inertia_page_has_a_nested_prop_with_a_value_using_a_responsable()
    {
        Model::unguard();
        $resource = JsonResource::make(User::make(['name' => 'Another']));

        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example' => [
                    'nested' => $resource,
                ],
            ])
        );

        $response->assertInertiaHas('example.nested', $resource);
    }

    public function test_the_inertia_page_does_not_have_a_nested_prop_with_a_value_using_a_responsable()
    {
        Model::unguard();
        $resourceA = JsonResource::make(User::make(['name' => 'Another']));
        $resourceB = JsonResource::make(User::make(['name' => 'Example']));

        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'example' => [
                    'nested' => $resourceA,
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaHas('example.nested', $resourceB);
    }

    public function test_the_inertia_page_has_all_the_given_props()
    {
        Model::unguard();
        $user = User::make(['name' => 'Example']);

        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => 'bar',
                'baz' => [
                    'nested' => 'value',
                    'another' => $user,
                    'closure' => 'test',
                ],
            ])
        );

        $response->assertInertiaHasAll([
            'foo',
            'foo' => 'bar',
            'baz.nested' => 'value',
            'baz.another' => $user,
            'baz.closure' => function ($value) {
                return $value === 'test';
            },
        ]);
    }

    public function test_the_inertia_page_does_not_have_all_the_given_props()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => 'bar',
                'baz' => [
                    'nested' => 'value',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaHasAll([
            'foo' => 'bar',
            'baz.nested' => 'value',
            'missing-key',
        ]);
    }

    public function test_the_inertia_page_is_missing_a_prop()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => 'bar',
            ])
        );

        $response->assertInertiaMissing('baz');
    }

    public function test_the_inertia_page_is_not_missing_a_prop()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => 'bar',
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaMissing('foo');
    }

    public function test_the_inertia_page_is_missing_a_nested_prop()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => 'bar',
                'baz' => [
                    'another' => 'value',
                ],
            ])
        );

        $response->assertInertiaMissing('baz.nested');
    }

    public function test_the_inertia_page_is_not_missing_a_nested_prop()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => 'bar',
                'baz' => [
                    'nested' => 'value',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaMissing('baz.nested');
    }

    public function test_it_can_retrieve_a_single_prop()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => [
                    'nested' => ['bar', 'baz'],
                ],
            ])
        );

        $this->assertCount(1, $response->inertiaProps('foo'));
    }

    public function test_it_does_not_have_a_single_prop()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => [
                    'nested' => ['bar', 'baz'],
                ],
            ])
        );

        $this->assertNull($response->inertiaProps('baz'));
    }

    public function test_it_can_retrieve_a_single_nested_prop()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => [
                    'nested' => ['bar', 'baz'],
                ],
            ])
        );

        $this->assertCount(2, $response->inertiaProps('foo.nested'));
    }

    public function test_it_does_not_have_a_single_nested_prop()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => [
                    'nested' => ['bar', 'baz'],
                ],
            ])
        );

        $this->assertNull($response->inertiaProps('foo.bar'));
    }

    public function test_the_inertia_page_is_matching_count()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => 'bar',
                'baz' => [
                    'nested' => 'value',
                    'other' => 'value',
                ],
            ])
        );

        $response->assertInertiaCount('baz', 2);
    }

    public function test_the_inertia_page_with_nested_key_is_matching_count()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => 'bar',
                'baz' => [
                    'nested' => [
                        'flim' => 'value',
                        'flam' => 'value',
                    ],
                ],
            ])
        );

        $response->assertInertiaCount('baz.nested', 2);
    }

    public function test_the_inertia_page_is_not_matching_count()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => 'bar',
                'baz' => [
                    'nested' => 'value',
                    'other' => 'value',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaCount('baz', 3);
    }

    public function test_the_inertia_page_fails_count_on_missing_prop()
    {
        $response = $this->makeMockResponse(
            Inertia::render('test-component', [
                'foo' => 'bar',
                'baz' => [
                    'nested' => 'value',
                    'other' => 'value',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);

        $response->assertInertiaCount('invalid', 2);
    }

    private function makeMockResponse($view)
    {
        app('router')->get('/', function () use ($view) {
            return $view;
        });

        return $this->get('/');
    }
}
