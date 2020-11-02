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

> **NOTE**: This package WILL be deprecated once ANY official testing helpers become available in [inertiajs/inertia-laravel](https://github.com/inertiajs/inertia-laravel). The package WILL stay available for install, but WILL NOT receive any further (security) updates from that point forward.

## Installation

You can install the package via composer:

```bash
composer require --dev claudiodekker/inertia-laravel-testing
```

## Usage

To test, simply chain any of the following methods onto your `TestResponse` responses.

![Screenshot 2020-09-02 at 19 44 39](https://user-images.githubusercontent.com/1752195/92017928-c10b4b00-ed54-11ea-95b4-ccff11d89d06.png)

## Available Methods
The methods made available in this package closely reflect those available in Laravel itself:

Assert whether the given page is an Inertia-rendered view
```php
$response->assertInertia();

// or, also check whether the page is a specific component
$response->assertInertia('example');

// or, also check whether all of the given props match
$response->assertInertia('example', [
    'foo' => 'bar'
]);
```

Return all available Inertia props for the page, or only retrieve a specific one
``` php
$response->inertiaProps();

// Retrieve a specific (nested) prop. Returns `null` if the prop doesn't exist.
$response->inertiaProps('nested.prop'); 
```

Assert whether the Inertia-rendered view has a specific property set
```php
$response->assertInertiaHas('key');

// or, against deeply nested values
$response->assertInertiaHas('deeply.nested.key');
```

Apart from checking whether the property is set, the same method can be used to assert that the values match
```php
$response->assertInertiaHas('key', 'matches-this-value');

// or, for deeply nested values
$response->assertInertiaHas('deeply.nested.key', 'also-match-against-this-value');
```

It's also possible to assert directly against a Laravel Model (or any other `Arrayable` or `Responsable` class)
```php
$user = UserFactory::new()->create(['name' => 'John Doe']);

// ... (Make HTTP request etc.)

$response->assertInertiaHas('user', $user);
$response->assertInertiaHas('deeply.nested.user', $user);
```

It's also possible to check against a closure
```php
$response->assertInertiaHas('foo', function ($value) {
    return $value === 'bar';
});

// or, again, for deeply nested values
$response->assertInertiaHas('deeply.nested.foo', function ($value) {
    return $value === 'bar';
});
```

Next, you can also check against a whole array of properties. It'll simply loop over them using the `assertInertiaHas` method described above:
```php
$response->assertInertiaHasAll([
    'foo',
    'bar.baz',
    'another.nested.key' => 'example-value'
]);
```

Finally, you can assert that a property was not set:
```php
$response->assertInertiaMissing('key');

// or, for deeply nested values
$response->assertInertiaMissing('deeply.nested.key');
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
