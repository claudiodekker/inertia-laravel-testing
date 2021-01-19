# Changelog

All notable changes to `claudiodekker/inertia-laravel-testing` will be documented in this file

## 2.2.3 - 2021-01-19
- Fail when an unsupported second argument is provided to `has` ([#28](https://github.com/claudiodekker/inertia-laravel-testing/pull/28))

## 2.2.2 - 2021-01-19
- Use `assertSame` instead of `assertEquals` for safer comparisons ([#27](https://github.com/claudiodekker/inertia-laravel-testing/pull/27))

## 2.2.1 - 2021-01-18
- Fixes a bug where the automatic property interaction check was also enabled on the top-level by default ([#26](https://github.com/claudiodekker/inertia-laravel-testing/pull/26))

## 2.2.0 - 2021-01-18
- Add `dump` and `dd` helpers by default ([#24](https://github.com/claudiodekker/inertia-laravel-testing/pull/24))
- Add & prefer `missing` & `missingAll` methods ([#23](https://github.com/claudiodekker/inertia-laravel-testing/pull/23))
- Fixes an issue where complex objects could not be scoped/asserted against correctly ([#22](https://github.com/claudiodekker/inertia-laravel-testing/pull/22))

## 2.1.0 - 2021-01-18
- Make the `Assert` class Macroable ([#20](https://github.com/claudiodekker/inertia-laravel-testing/pull/20))
- Provide a `dump` and `dd` macro that can be mixed in using `Assert::mixin(new VarDumper());`

## 2.0.0 - 2021-01-18
- Reworked the entire library using a brand-new, fluent syntax ([#18](https://github.com/claudiodekker/inertia-laravel-testing/pull/18))
- Updated deprecation notice: This version serves as the **official beta** for [inertiajs/inertia-laravel](https://github.com/inertiajs/inertia-laravel).
- `$response->assertInertia('component')` now also asserts that the component exists on the filesystem ([#17](https://github.com/claudiodekker/inertia-laravel-testing/pull/17))

## 1.1.0 - 2020-09-10
- Added the ability to select a single (nested) prop using `$response->inertiaProps('nested.prop')`

## 1.0.0 - 2020-09-03
- Initial release based on [this PR](https://github.com/inertiajs/inertia-laravel/pull/124)
