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

To start testing your Inertia pages, simply call the `assertInertia` method on your `TestResponse` responses, and chain any of the [available assertions](#available-assertions) on its closure/callback argument:

```php
$response->assertInertia(fn ($page) => $page->someInertiaAssertion());
```

When using this library to its fullest extent, your tests will end up looking similar to this:
```php
use ClaudioDekker\Inertia\Assert;

$response->assertInertia(fn (Assert $page) => $page
    ->component('Podcasts/Show')
    ->has('podcast', fn (Assert $page) => $page
        ->where('id', $podcast->id)
        ->where('subject', 'The Laravel Podcast')
        ->where('description', 'The Laravel Podcast brings you Laravel and PHP development news and discussion.')
        ->has('seasons', 4)
        ->has('seasons.4.episodes', 21)
        ->has('host', fn (Assert $page) => $page
            ->where('id', 1)
            ->where('name', 'Matt Stauffer')
        )
        ->has('subscribers', 7, fn (Assert $page) => $page
            ->where('id', 2)
            ->where('name', 'Claudio Dekker')
            ->where('platform', 'Apple Podcasts')
            ->etc()
            ->missing('email')
            ->missing('password')
        )
    )
);
```

> **NOTE**: The above uses [arrow functions](https://www.php.net/manual/en/migration74.new-features.php#migration74.new-features.core.arrow-functions), which are available as of PHP 7.4+.
> If you are using this library on an older version of PHP, you will unfortunately need to use a regular callback instead:
> ```php
> $response->assertInertia(function (Assert $page) {
>     $page
>         ->component('Podcasts/Show')
>         ->has('podcast', /* ...*/);
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
    - [`missing`](#missing)

Reducing verbosity (multiple assertions):
- [`has`](#has-1)
- [`where`](#where-1)
- [`missing`](#missing-1)

Helpers:
- [Debugging](#debugging)

---

## Component

To assert that the Inertia page has the page component you expect, you can use the `component` assertion:

```php
$response->assertInertia(fn (Assert $page) => $page->component('Podcasts/Show'));
```

Apart from asserting that the component matches what you expect, this assertion will also automatically attempt to
locate the page component on the filesystem, and will fail when it cannot be found.

> **NOTE**: By default, lookup occurs relative to the `resources/js/Pages` folder, and will only accept matching files that have a `.vue` or `.svelte` extension.
All of these settings are configurable in our [configuration file](#publishing-the-configuration-file).
>
> **If you are missing any default extensions** (such as those for React), please let us know which ones should be supported by [opening an issue](https://github.com/claudiodekker/inertia-laravel-testing/issues/new)!

### Disabling or enabling a single lookup

To disable this filesystem lookup on a per-assertion basis, you may pass `false` as the second argument:

```php
$response->assertInertia(fn (Assert $page) => $page->component('Podcasts/Show', false));
```

Alternatively, if you've disabled the [automatic component filesystem lookup in the configuration file](#publishing-the-configuration-file), it's possible to do the opposite and instead enable the lookup on a per-assertion basis by passing `true` as the second argument.

## (Page) URL

To assert that the Page URL matches what you expect, you may use the `url` assertion:

```php
$response->assertInertia(fn (Assert $page) => $page->url('/podcasts'));
```

## (Asset) Version

To assert that the (asset) version matches what you expect, you may use the `version` assertion:

```php
$expected = md5(mix('/js/app.js'));

$response->assertInertia(fn (Assert $page) => $page->version($expected));
```

> **NOTE**: We recommend to only use this assertion when you are using [asset versioning](https://inertiajs.com/asset-versioning).

## `has`
### Basic Usage
To assert that Inertia **has** a property, you may use the `has` method. You can think of `has` similar to PHP's `isset`:

```php
$response->assertInertia(fn (Assert $page) => $page
    // Checking a root-level property
    ->has('podcast')

    // Checking that the podcast prop has a nested id property using "dot" notation
    ->has('podcast.id')
);
```

### Count / Size / Length
To assert that Inertia **has a certain amount of items**, you may provide the expected size as the second argument:
```php
$response->assertInertia(fn (Assert $page) => $page
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
the more complex your assertions became. For instance, here is a real example of some assertion logic we used to write:
```php
$response->assertInertiaHas('message.comments.0.files.0.url', '/storage/attachments/example-attachment.pdf');
$response->assertInertiaHas('message.comments.0.files.0.name', 'example-attachment.pdf');
```

Fortunately, we no longer _have to_ do this. Instead, we can simply scope properties using the `has` method:
```php
$response->assertInertia(fn (Assert $page) => $page
    // Creating a single-level property scope
    ->has('message', fn (Assert $page) => $page
        // We can now continue chaining methods
        ->has('subject')
        ->has('comments', 5)

        // And can even create a deeper scope using "dot" notation
        ->has('comments.0', fn (Assert $page) => $page
            ->has('body')
            ->has('files', 1)
            ->has('files.0', fn (Assert $page) => $page
                ->has('url')
            )
        )
    )
);
```

While this is already a significant improvement, that's not all: As you can see in the example above, you'll often run 
into situations where you'll want to _check that a property has a certain length_, and then tap into one of the entries
to make sure that all the props there are as expected:

```php
    ->has('comments', 5)
    ->has('comments.0', fn (Assert $page) => $page
        // ...
```

To simplify this, you can simply combine the two calls, providing the scope as the third argument:
```php
$response->assertInertia(fn (Assert $page) => $page
    // Assert that there are five comments, and automatically scope into the first comment.
    ->has('comments', 5, fn(Assert $page) => $page
        ->has('body')
        // ...
    )
);
```

## `where`
To assert that an Inertia property has an expected value, you may use the `where` assertion:

```php
$response->assertInertia(fn (Assert $page) => $page
    ->has('message', fn (Assert $page) => $page
        // Assert that the subject prop matches the given message
        ->where('subject', 'This is an example message')

        // or, the exact same, but for deeply nested values
        ->where('comments.0.files.0.name', 'example-attachment.pdf')
    )
);
```

Under the hood, this first calls the `has` method to ensure that the property exists, and then uses an assertion to 
make sure that the values match. This means that there is no need to manually call `has` and `where` on the same exact prop.

#### Automatic Eloquent `Model`, `Arrayable`, or `Responsable` transforming

For convenience, the `where` method doesn't just assert using basic JSON values, but also has the ability to
test directly against Eloquent Models, classes that implement the `Arrayable` or `Responsable` interfaces.

For example:
```php
$user = User::factory()->create(['name' => 'John Doe']);

// ... (Make your HTTP request etc.)

$response->assertInertia(fn (Assert $page) => $page
    ->where('user', $user)
    ->where('deeply.nested.user', $user)
);
```

### Using a Closure

Finally, it's also possible to assert against a callback / closure. To do so, simply provide a callback as the value,
and make sure that the response is `true` in order to make the assertion pass, or anything else to fail the assertion:

```php
$response->assertInertia(fn (Assert $page) => $page
    ->where('foo', fn ($value) => $value === 'bar')

    // or, as expected, for deeply nested values:
    ->where('deeply.nested.foo', function ($value) {
        return $value === 'bar';
    })
);
```

Because working with arrays directly isn't always a great experience, we'll automatically cast arrays to 
[Collections](https://laravel.com/docs/collections):
```php
$response->assertInertia(fn (Assert $page) => $page
    ->where('foo', function (Collection $value) {
        return $value->median() === 1.5;
    })
);
```

## `etc`
This library will automatically fail your test when you haven't interacted with at least one of the props in a scope,
which is generally a very useful feature. However, at times, you might run into situations where you're working with 
unreliable data (such as from a feed), or with data that you really don't want interact with to keep your test simple. 
For those situations, the `etc` method exists:

```php
$response->assertInertia(fn (Assert $page) => $page
    ->has('message', fn (Assert $page) => $page
        ->has('subject')
        ->has('comments')
        ->etc()
    )
);
```

> **NOTE**: This automatic property check ONLY applies to scopes (such as the `message` scope in the example above). If
> you wish to enforce this for the top-level of your page as well, you may enable this in our [configuration file](#publishing-the-configuration-file)..

> **NOTE**: While `etc` reads fluently at the end of a query scope, placing it at the beginning or somewhere in the
> middle of your assertions does not change how it behaves: It will disable the automatic check that asserts that all properties
> in the current scope have been interacted with.

### `missing`
Because `missing` isn't necessary by default, it provides a great solution when using `etc`. 

In short, it does the exact opposite of the `has` method, ensuring that the property does _not exist_:
```php
$response->assertInertia(fn (Assert $page) => $page
    ->has('message', fn (Assert $page) => $page
        ->has('subject')
        ->missing('published_at')
        ->etc()
    )
);
```

## Reducing verbosity
To reduce the amount of `where`, `has` or `missing` calls, there are a couple of convenience methods that allow you to
make these same assertions in a slightly less-verbose looking way. Do note that these methods do not make your assertions
any faster, and really only exist to help you reduce your test's visual complexity.

### `has`
Instead of making multiple `has` calls, you may use the `hasAll` assertion instead. Depending on how you provide 
arguments, this method will perform a series of slightly different but predictable assertion:

#### Basic `has` usage
```php
$response->assertInertia(fn (Assert $page) => $page
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
$response->assertInertia(fn (Assert $page) => $page
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
other methods, meaning that only the array-syntax exists for it right now:

```php
$response->assertInertia(fn (Assert $page) => $page
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

### `missing`
Instead of making multiple `missing` call, you may use `missingAll` instead. 

Similar to basic `hasAll` usage, this assertion accepts both a single array or a list of arguments, at which point it 
will assert that the given props do not exist:

```php
$response->assertInertia(fn (Assert $page) => $page
    // Before
    ->missing('subject')
    ->missing('user.name')

    // After
    ->missingAll([
        'subject',
        'user.name',
    ])

    // Alternative
    ->missingAll('subject', 'user.name')
);
```

## Debugging

While writing your tests, you might find yourself wanting to inspect some of the page's props using Laravel's 
`dump` or `dd` helpers. Luckily, this is really easy to do, and would work more or less how you'd expect it to:

```php
$response->assertInertia(fn (Assert $page) => $page
    // Dumping all props in the current scope
    // while still running all other assertions
    ->dump()
    ->where('user.name', 'Claudio')

    // Dump-and-die all props in the current scope, preventing
    // all other (perhaps failing) assertions from running
    ->dd()
    ->where('user.name', 'Jonathan')

    // Dumping / Dump-and-die a specific prop
    ->dump('user')
    ->dd('user.name')
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
