![inertiajs/inertia-laravel Testing Helpers](https://banners.beyondco.de/Inertia%20Laravel%20Testing%20Helpers.png?theme=light&packageName=--dev+claudiodekker%2Finertia-laravel-testing&pattern=circuitBoard&style=style_1&description=Assertions+to+help+ensure+that+an+InertiaJS-compatible+response+is+sent&md=1&fontSize=100px&images=check-circle)

<p align="center">
  <a href="https://github.com/claudiodekker/inertia-laravel-testing/releases">
    <img src="https://img.shields.io/github/release/claudiodekker/inertia-laravel-testing.svg?style=flat-square" alt="Latest Version">
  </a>
  <a href="https://github.com/claudiodekker/inertia-laravel-testing/actions?query=workflow%3Atests+branch%3Amaster">
    <img src="https://img.shields.io/github/workflow/status/claudiodekker/inertia-laravel-testing/tests/master.svg?style=flat-square" alt="Build Status">
  </a>
  <a href="https://scrutinizer-ci.com/g/claudiodekker/inertia-laravel-testing">
    <img src="https://img.shields.io/scrutinizer/g/claudiodekker/inertia-laravel-testing.svg?style=flat-square" alt="Quality Score">
  </a>
  <a href="https://styleci.io/repos/292526547"><img src="https://styleci.io/repos/292526547/shield" alt="StyleCI"></a>
  <a href="https://packagist.org/packages/claudiodekker/inertia-laravel-testing">
    <img src="https://img.shields.io/packagist/dt/claudiodekker/inertia-laravel-testing.svg?style=flat-square" alt="Total Downloads">
  </a>
</p>

# [inertiajs/inertia-laravel](https://github.com/inertiajs/inertia-laravel) Testing Helpers

> **GREAT NEWS!**: This package will be merged into [inertiajs/inertia-laravel](https://github.com/inertiajs/inertia-laravel) on **March 1st, 2021**, with upgrading efforts only taking a couple of seconds.  Once this happens, this package WILL remain available for installation, but WILL NOT receive any further (security) updates going forward.

## Installation

You can install the package via composer:

```bash
composer require --dev claudiodekker/inertia-laravel-testing
```

## Usage

To start testing your Inertia pages, simply call the `assertInertia` method on your `TestResponse` responses, and chain any of the [available assertions](#available-assertions) on it's closure/callback argument:

```php
$response->assertInertia(fn ($inertia) => $inertia->someInertiaAssertion());
```

When using this library to it's fullest extent, your tests will end up looking similar to this:
```php
use ClaudioDekker\Inertia\Assert;

$response->assertInertia(fn (Assert $inertia) => $inertia
    ->component('Podcasts/Show')
    ->has('podcast', fn (Assert $inertia) => $inertia
        ->where('id', $podcast->id)
        ->where('subject', 'The Laravel Podcast')
        ->where('description', 'The Laravel Podcast brings you Laravel and PHP development news and discussion.')
        ->has('seasons', 4)
        ->has('seasons.4.episodes', 21)
        ->has('host', fn (Assert $inertia) => $inertia
            ->where('id', 1)
            ->where('name', 'Matt Stauffer')
        )
        ->has('subscribers', 7, fn (Assert $inertia) => $inertia
            ->where('id', 2)
            ->where('name', 'Claudio Dekker')
            ->where('platform', 'Apple Podcasts')
            ->etc()
            ->misses('email')
            ->misses('password')
        )
    )
);
```

> **NOTE**: The above uses [arrow functions](https://www.php.net/manual/en/migration74.new-features.php#migration74.new-features.core.arrow-functions), which are available as of PHP 7.4+.
> If you are using this package on an older version of PHP, you will unfortunately need to use a regular callback instead:
> ```php
> $response->assertInertia(function (Assert $inertia) {
>     $inertia
>            ->component('Podcasts/Show')
>            ->has('podcast', /* ...*/);
> });
> ```

> **NOTE**: While type-hinting the `Assert` isn't necessary (and will cause _some_ minor search-and-replaceable breakage once migrating away from this package), it allows your IDE to automatically suggest the assertion methods that can be chained.

## Available Assertions

Basics:
- [Component](#component)
- [(Page) URL](#page-url)
- [(Asset) Version](#asset-version)

In-depth:
- [`has`](#has)
    - [Count / Size / Length](#count--size--length)
    - [Scoping](#scoping)
- [`where`](#where)
    - [Using a Closure](#using-a-closure)
- [`etc`](#etc)
    - [`misses`](#misses)

Reducing verbosity:
- [`has`](#has-1)
- [`where`](#where-1)
- [`misses`](#misses-1)

---

## Component

To assert that the Inertia page has the page component you expect, you can use the `component` assertion:

```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    ->component('Podcasts/Show')
);
```

Apart from asserting that the component matches what you expect, this assertion will also automatically attempt to
locate the page component on the filesystem, and will fail when it cannot be found.

By default, lookup occurs relative to the `resources/js/Pages` folder, and will only accept matching files that have a `.vue` or `.svelte` extension.
All of these settings are configurable using our [configuration file](#publishing-the-configuration-file).

> **NOTE:** If you are missing any logical default extensions such as those for React, please let us know which ones should be supported by [opening an issue](https://github.com/claudiodekker/inertia-laravel-testing/issues/new)!

### Disabling or enabling a single lookup

To disable this filesystem lookup on a per-assertion basis, you may pass `false` as the second argument:

```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    ->component('Podcasts/Show', false)
);
```

Alternatively, if you've disabled the [automatic component filesystem lookup in the configuration file](#publishing-the-configuration-file), you can enable the lookup on a per-assertion basis by passing `true` as the second argument instead.

## (Page) URL

To assert that the Page URL matches what you expect, you may use the `url` assertion:

```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    ->url('/podcasts')
);
```

## (Asset) Version

To assert that the (asset) version matches what you expect, you may use the `version` assertion:

```php
$expected = md5(mix('/js/app.js'));

$response->assertInertia(fn (Assert $inertia) => $inertia
    ->version($expected)
);
```

> **NOTE**: We recommend to only use this assertion when you are using [asset versioning](https://inertiajs.com/asset-versioning).

## `has`
### Basic Usage
To assert that Inertia **has** a property, you may use the `has` method:

```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    // Checking a root-level property
    ->has('podcast')

    // Checking that the podcast prop has a nested id property using "dot" notation
    ->has('podcast.id')
);
```

### Count / Size / Length
To assert that Inertia **has** a property of a specific size/length, you may provide the expected size as the second argument:
```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    // Checking that the root-level podcasts property exists and has 7 items
    ->has('podcast', 7)

    // Checking that the podcast has 11 subscribers using "dot" notation
    ->has('podcast.subscribers', 11)
);
```

The above will first assert that the property exists, as well as that is the expected size.
This means that there is no need to manually ensure that the property exists using a separate `has` call.

### Scoping

In a previous version of this library, testing code could become fairly verbose, and the deeper your assertions went, 
the more complex your assertions became. For instance, here is an example of code we used to write, taken directly from 
one of our projects:
```php
$response->assertInertiaHas('message.comments.0.files.0.url', '/storage/attachments/example-attachment.pdf');
$response->assertInertiaHas('message.comments.0.files.0.name', 'example-attachment.pdf');
```

Fortunately, we no longer have to do this. Instead, we can simply scope properties using the `has` method:
```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    // Creating a single-level property scope
    ->has('message', fn (Assert $inertia) => $inertia
        // We can now continue chaining methods
        ->has('subject')
        ->has('comments', 5)

        // And can even create a deeper scope using "dot" notation
        ->has('comments.0', fn (Assert $inertia) => $inertia
            ->has('body')
            ->has('files', 1)
            ->has('files.0', fn (Assert $inertia) => $inertia
                ->has('url')
            )
        )
    )
);
```

While this is already a significant improvement, that's not all: As you can see in the example above, you'll often run 
into situations where you'll want to _check that a property has a certain length_, and then tap into one of the entries
to make sure that all the props there are as expected.

To simplify this, you can simply combine the two calls:
```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    ->has('message', fn (Assert $inertia) => $inertia
        ->has('subject')
        // Assert that there are five comments, and automatically scope into the first comment.
        ->has('comments', 5, fn(Assert $inertia) => $inertia
            ->has('body')
            ->has('files', 1, fn (Assert $inertia) => $inertia
                ->has('url')
            )
        )
    )
);
```

## `where`

So far, we've primarily been describing how you can check that Inertia props exist and how to count them, but we haven't
actually described how to assert that an Inertia property has a value. This can be done using `where`:

```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    ->has('message', fn (Assert $inertia) => $inertia
        // Assert that the subject prop matches the given message
        ->where('subject', 'This is an example message')

        // or, the exact same, but for deeply nested values
        ->where('comments.0.files.0.name', 'example-attachment.pdf')
    )
);
```

Under the hood, this first calls the `has` method to ensure that the property exists, and then uses an assertion to 
make sure that the values match. This means that there is no need to manually call `has` and `where` on the same prop.

#### Automatic Eloquent `Model`, `Arrayable`, or `Responsable` transforming

For convenience, the `where` method doesn't just assert that strings or integers match, but also has the ability to
test directly against Eloquent Models, classes that implement the `Arrayable` interface, or the `Responsable` interface.

For example:
```php
$user = User::factory()->create(['name' => 'John Doe']);

// ... (Make your HTTP request etc.)

$response->assertInertia(fn (Assert $inertia) => $inertia
    ->where('user', $user)
    ->where('deeply.nested.user', $user)
);
```

### Using a Closure

Finally, it's also possible to assert against a callback / closure. To do so, simply provide a callback as the value,
and make sure that the response is `true` in order to make the assertion pass, or anything else to fail the assertion:

```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    ->where('foo', fn ($value) => $value === 'bar')

    // or, as expected, for deeply nested values:
    ->where('deeply.nested.foo', function ($value) {
        return $value === 'bar';
    })
);
```

## `etc`
By default, this package will automatically make sure that you didn't forget to assert against some props, by detecting
what props you've interacted with. However, at times, you might run into situations where you're working with unreliable
data (such as from a feed) or with data that you really don't want to do anything with. For those situations, the `etc`
method exists:

```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    ->has('message', fn (Assert $inertia) => $inertia
        ->has('subject')
        ->has('comments')
        ->etc()
    )
);
```

> **NOTE**: While `etc` reads fluently at the end of a query scope, placing it at the very beginning or somewhere in the
> middle of your assertions does not change how it behaves: It will disable the automatic check that asserts that all properties
> in the current scope have been interacted with.

### `misses`

While `misses` isn't necessary by default (as this library automatically checks that you've asserted/interacted with
each property and will if you haven't), it provides a great solution when using `etc.`

In short, it does exactly the opposite as the (basic usage of the) `has` method: It ensures that the prop does 
_not exist_.

```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    ->has('message', fn (Assert $inertia) => $inertia
        ->has('subject')
        ->misses('published_at')
        ->etc()
    )
);
```

## Reducing verbosity
To reduce the amount of `where`, `has` or `misses` calls, there are a couple of convenience methods that allow you to
make these same assertions in a slightly less-verbose way.

### `has`
Instead of making multiple `has` calls, you may use the `hasAll` assertion instead. Depending on how you provide 
arguments, this method will perform a series of slightly different but predictable assertion:

#### Basic `has` usage
```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    // Before
    ->has('messages')
    ->has('subscribers')

    // After
    ->hasAll([
        'messages',
        'subscribers',
    ])

    // Alternative
    ->hasAll('messages', 'subscribers')
);
```

#### Count / Size / Length
```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    // Before
    ->has('messages', 5)
    ->has('subscribers', 11)

    // After
    ->hasAll([
        'messages' => 5,
        'subscribers' => 11,
    ])
);
```

### `where`
To reduce the amount of `where` calls, the `whereAll` method exists.

Since this method checks properties against values by design, there isn't a lot of flexibility like with some of these
other methods, and only the array-syntax exists as of right now:

```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    // Before
    ->where('subject', 'Hello World')
    ->has('user.name', 'Claudio')

    // After
    ->whereAll([
        'subject' => 'Hello World',
        'user.name' => fn ($value) => $value === 'Claudio',
    ])
);
```

### `misses`
Instead of making multiple `misses` call, you may use `missesAll` instead. Similar to basic `hasAll` usage, this 
assertion accepts both a single array or a list of arguments, at which point it will assert that the given props
do not exist.

```php
$response->assertInertia(fn (Assert $inertia) => $inertia
    // Before
    ->misses('subject')
    ->misses('user.name')

    // After
    ->missesAll([
        'subject',
        'user.name',
    ])

    // Alternative
    ->missesAll('subject', 'user.name')
);
```

## Publishing the configuration file

To modify any settings such as the lookup paths, valid extensions etc., you may publish our configuration file into your
application and change any of it's values. To do so, run the following Artisan command:

```bash
php artisan vendor:publish --provider="ClaudioDekker\Inertia\InertiaTestingServiceProvider"
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email claudio@ubient.net instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
