<?php

namespace ClaudioDekker\Inertia\Tests\Unit;

use ClaudioDekker\Inertia\Assert;
use ClaudioDekker\Inertia\Tests\TestCase;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
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
    public function it_preserves_the_ability_to_continue_chaining_laravel_test_response_calls(): void
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

        config()->set('inertia.page.should_exist', true);
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

        config()->set('inertia.page.should_exist', true);
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia page component file [foo] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('foo');
        });
    }

    /** @test */
    public function it_can_force_enable_the_component_file_existence(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        config()->set('inertia.page.should_exist', false);
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia page component file [foo] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('foo', true);
        });
    }

    /** @test */
    public function it_can_force_disable_the_component_file_existence_check(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        config()->set('inertia.page.should_exist', true);

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('foo', false);
        });
    }

    /** @test */
    public function the_component_does_not_exist_on_the_filesystem_when_it_does_not_exist_relative_to_any_of_the_given_paths(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('fixtures/ExamplePage')
        );

        config()->set('inertia.page.should_exist', true);
        config()->set('inertia.page.paths', [realpath(__DIR__)]);
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

        config()->set('inertia.page.should_exist', true);
        config()->set('inertia.page.extensions', ['bin', 'exe', 'svg']);
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia page component file [fixtures/ExamplePage] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->component('fixtures/ExamplePage');
        });
    }

    /** @test */
    public function it_has_a_prop(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'prop' => 'value',
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('prop');
        });
    }

    /** @test */
    public function it_does_not_have_a_prop(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => 'value',
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [prop] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('prop');
        });
    }

    /** @test */
    public function it_has_a_nested_prop(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'example' => [
                    'nested' => 'nested-value',
                ],
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('example.nested');
        });
    }

    /** @test */
    public function it_does_not_have_a_nested_prop(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'example' => [
                    'nested' => 'nested-value',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [example.another] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('example.another');
        });
    }

    /** @test */
    public function it_can_count_the_amount_of_items_in_a_given_prop(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => [
                    'baz' => 'example',
                    'prop' => 'value',
                ],
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('bar', 2);
        });
    }

    /** @test */
    public function it_fails_counting_when_the_amount_of_items_in_a_given_prop_does_not_match(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => [
                    'baz' => 'example',
                    'prop' => 'value',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [bar] does not have the expected size.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('bar', 1);
        });
    }

    /** @test */
    public function it_cannot_count_the_amount_of_items_in_a_given_prop_when_the_prop_does_not_exist(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => [
                    'baz' => 'example',
                    'prop' => 'value',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [baz] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('baz', 2);
        });
    }

    /** @test */
    public function it_asserts_that_a_prop_is_missing(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'foo' => [
                    'bar' => true,
                ],
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->misses('foo.baz');
        });
    }

    /** @test */
    public function it_fails_asserting_that_a_prop_is_missing_when_it_exists(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'prop' => 'value',
                'foo' => [
                    'bar' => true,
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [foo.bar] was found while it was expected to be missing.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia
                ->has('prop')
                ->misses('foo.bar');
        });
    }

    /** @test */
    public function it_can_assert_that_multiple_props_are_missing(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'baz' => 'foo',
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia
                ->has('baz')
                ->missesAll([
                    'foo',
                    'bar',
                ]);
        });
    }

    /** @test */
    public function it_cannot_assert_that_multiple_props_are_missing_when_at_least_one_exists(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'foo' => 'bar',
                'baz' => 'example',
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [baz] was found while it was expected to be missing.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia
                ->has('foo')
                ->missesAll([
                    'bar',
                    'baz',
                ]);
        });
    }

    /** @test */
    public function it_can_use_arguments_instead_of_an_array_to_assert_that_it_misses_multiple_props(): void
    {
        $this->makeMockRequest(
                Inertia::render('foo', [
                    'baz' => 'foo',
                ])
        )->assertInertia(function (Assert $inertia) {
            $inertia->has('baz')->missesAll('foo', 'bar');
        });

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [baz] was found while it was expected to be missing.');

        $this->makeMockRequest(
            Inertia::render('foo', [
                'foo' => 'bar',
                'baz' => 'example',
            ])
        )->assertInertia(function (Assert $inertia) {
            $inertia->has('foo')->missesAll('bar', 'baz');
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
    public function the_prop_matches_a_value_using_a_closure(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => 'baz',
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->where('bar', function ($value) {
                return $value === 'baz';
            });
        });
    }

    /** @test */
    public function the_prop_does_not_match_a_value_using_a_closure(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => 'baz',
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [bar] was marked as invalid using a closure.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->where('bar', function ($value) {
                return $value === 'invalid';
            });
        });
    }

    /** @test */
    public function array_props_will_be_automatically_cast_to_collections_when_using_a_closure(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => [
                    'baz' => 'foo',
                    'example' => 'value',
                ],
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->where('bar', function ($value) {
                $this->assertInstanceOf(Collection::class, $value);

                return $value->count() === 2;
            });
        });
    }

    /** @test */
    public function the_prop_matches_a_value_using_an_arrayable(): void
    {
        Model::unguard();
        $user = User::make(['name' => 'Example']);
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => $user,
            ])
        );

        $response->assertInertia(function (Assert $inertia) use ($user) {
            $inertia->where('bar', $user);
        });
    }

    /** @test */
    public function the_prop_does_not_match_a_value_using_an_arrayable(): void
    {
        Model::unguard();
        $userA = User::make(['name' => 'Example']);
        $userB = User::make(['name' => 'Another']);
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => $userA,
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [bar] does not match the expected Arrayable.');

        $response->assertInertia(function (Assert $inertia) use ($userB) {
            $inertia->where('bar', $userB);
        });
    }

    /** @test */
    public function the_prop_matches_a_value_using_a_responsable(): void
    {
        Model::unguard();
        $user = User::make(['name' => 'Example']);
        $resource = JsonResource::collection(new Collection([$user, $user]));
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => $resource,
            ])
        );

        $response->assertInertia(function (Assert $inertia) use ($resource) {
            $inertia->where('bar', $resource);
        });
    }

    /** @test */
    public function the_prop_does_not_match_a_value_using_a_responsable(): void
    {
        Model::unguard();
        $resourceA = JsonResource::make(User::make(['name' => 'Another']));
        $resourceB = JsonResource::make(User::make(['name' => 'Example']));
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => $resourceA,
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [bar] does not match the expected Responsable.');

        $response->assertInertia(function (Assert $inertia) use ($resourceB) {
            $inertia->where('bar', $resourceB);
        });
    }

    /** @test */
    public function the_nested_prop_matches_a_value(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'example' => [
                    'nested' => 'nested-value',
                ],
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->where('example.nested', 'nested-value');
        });
    }

    /** @test */
    public function the_nested_prop_does_not_match_a_value(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'example' => [
                    'nested' => 'nested-value',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [example.nested] does not match the expected value.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->where('example.nested', 'another-value');
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
                ],
            ])
        );

        $called = false;
        $response->assertInertia(function (Assert $inertia) use (&$called) {
            $inertia->has('bar', function (Assert $inertia) use (&$called) {
                $called = true;
                $inertia
                    ->where('baz', 'example')
                    ->where('prop', 'value');
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
                ],
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
                'bar' => 'value',
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

    /** @test */
    public function it_can_directly_scope_onto_the_first_item_when_asserting_that_a_prop_has_a_length_greater_than_zero(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => [
                    ['key' => 'first'],
                    ['key' => 'second'],
                ],
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('bar', 2, function (Assert $inertia) {
                $inertia->where('key', 'first');
            });
        });
    }

    /** @test */
    public function it_cannot_directly_scope_onto_the_first_item_when_asserting_that_a_prop_has_a_length_of_zero(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => [
                    ['key' => 'first'],
                    ['key' => 'second'],
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Cannot scope directly onto the first entry of property [bar] when asserting that it has a size of 0.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('bar', 0, function (Assert $inertia) {
                $inertia->where('key', 'first');
            });
        });
    }

    /** @test */
    public function it_cannot_directly_scope_onto_the_first_item_when_it_does_not_match_the_expected_size(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => [
                    ['key' => 'first'],
                    ['key' => 'second'],
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [bar] does not have the expected size.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('bar', 1, function (Assert $inertia) {
                $inertia->where('key', 'first');
            });
        });
    }

    /** @test */
    public function it_fails_when_it_does_not_interact_with_all_props_in_the_scope_at_least_once(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => [
                    'baz' => 'example',
                    'prop' => 'value',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Unexpected Inertia properties were found in scope [bar].');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('bar', function (Assert $inertia) {
                $inertia->where('baz', 'example');
            });
        });
    }

    /** @test */
    public function it_fails_when_it_does_not_interact_with_all_props_on_the_root_level_at_least_once(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'foo' => 'bar',
                'bar' => 'baz',
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Unexpected Inertia properties were found on the root level.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->has('foo');
        });
    }

    /** @test */
    public function it_can_disable_the_interaction_check_for_the_current_scope(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => true,
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->etc();
        });
    }

    /** @test */
    public function it_cannot_disable_the_interaction_check_for_any_other_scopes(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => true,
                'baz' => [
                    'foo' => 'bar',
                    'example' => 'value',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Unexpected Inertia properties were found in scope [baz].');

        $response->assertInertia(function (Assert $inertia) {
            $inertia
                ->etc()
                ->has('baz', function (Assert $inertia) {
                    $inertia->where('foo', 'bar');
                });
        });
    }

    /** @test */
    public function it_can_assert_that_multiple_props_match_their_expected_values_at_once(): void
    {
        Model::unguard();
        $user = User::make(['name' => 'Example']);
        $resource = JsonResource::make(User::make(['name' => 'Another']));

        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'foo' => [
                    'user' => $user,
                    'resource' => $resource,
                ],
                'bar' => 'baz',
            ])
        );

        $response->assertInertia(function (Assert $inertia) use ($user, $resource) {
            $inertia->whereAll([
                'foo.user' => $user,
                'foo.resource' => $resource,
                'bar' => function ($value) {
                    return $value === 'baz';
                },
            ]);
        });
    }

    /** @test */
    public function it_cannot_assert_that_multiple_props_match_their_expected_values_when_at_least_one_does_not(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'foo' => 'bar',
                'baz' => 'example',
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [baz] was marked as invalid using a closure.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->whereAll([
                'foo' => 'bar',
                'baz' => function ($value) {
                    return $value === 'foo';
                },
            ]);
        });
    }

    /** @test */
    public function it_can_assert_that_it_has_multiple_props(): void
    {
        Model::unguard();
        $user = User::make(['name' => 'Example']);
        $resource = JsonResource::make(User::make(['name' => 'Another']));

        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'foo' => [
                    'user' => $user,
                    'resource' => $resource,
                ],
                'bar' => 'baz',
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->hasAll([
                'foo.user',
                'foo.resource',
                'bar',
            ]);
        });
    }

    /** @test */
    public function it_cannot_assert_that_it_has_multiple_props_when_at_least_one_is_missing(): void
    {
        Model::unguard();
        $user = User::make(['name' => 'Example']);
        $resource = JsonResource::make(User::make(['name' => 'Another']));

        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'foo' => [
                    'user' => $user,
                    'resource' => $resource,
                ],
                'bar' => 'baz',
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [baz] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->hasAll([
                'foo.user',
                'baz',
            ]);
        });
    }

    /** @test */
    public function it_can_use_arguments_instead_of_an_array_to_assert_that_it_has_multiple_props(): void
    {
        Model::unguard();
        $user = User::make(['name' => 'Example']);
        $resource = JsonResource::make(User::make(['name' => 'Another']));

        $this->makeMockRequest(
            Inertia::render('foo', [
                'foo' => [
                    'user' => $user,
                    'resource' => $resource,
                ],
                'bar' => 'baz',
            ])
        )->assertInertia(function (Assert $inertia) {
            $inertia->hasAll('foo.user', 'foo.resource', 'bar');
        });

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [baz] does not exist.');

        $this->makeMockRequest(
            Inertia::render('foo', [
                'foo' => [
                    'user' => $user,
                    'resource' => $resource,
                ],
                'bar' => 'baz',
            ])
        )->assertInertia(function (Assert $inertia) {
            $inertia->hasAll('foo.user', 'baz');
        });
    }

    /** @test */
    public function it_can_count_multiple_props_at_once(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => [
                    'key' => 'value',
                    'prop' => 'example',
                ],
                'baz' => [
                    'another' => 'value',
                ],
            ])
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->hasAll([
                'bar' => 2,
                'baz' => 1,
            ]);
        });
    }

    /** @test */
    public function it_cannot_count_multiple_props_at_once_when_at_least_one_is_missing(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo', [
                'bar' => [
                    'key' => 'value',
                    'prop' => 'example',
                ],
            ])
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia property [baz] does not exist.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->hasAll([
                'bar' => 2,
                'baz' => 1,
            ]);
        });
    }

    /** @test */
    public function the_page_url_matches(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->url('/example-url');
        });
    }

    /** @test */
    public function the_page_url_does_not_match(): void
    {
        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Unexpected Inertia page url.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->url('/invalid-page');
        });
    }

    /** @test */
    public function the_asset_version_matches(): void
    {
        Inertia::version('example-version');

        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        $response->assertInertia(function (Assert $inertia) {
            $inertia->version('example-version');
        });
    }

    /** @test */
    public function the_asset_version_does_not_match(): void
    {
        Inertia::version('example-version');

        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Unexpected Inertia asset version.');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->version('different-version');
        });
    }

    /** @test */
    public function it_is_macroable(): void
    {
        Assert::macro('myCustomMacro', function () {
            throw new Exception('My Custom Macro was called!');
        });

        $response = $this->makeMockRequest(
            Inertia::render('foo')
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('My Custom Macro was called!');

        $response->assertInertia(function (Assert $inertia) {
            $inertia->myCustomMacro();
        });
    }
}
