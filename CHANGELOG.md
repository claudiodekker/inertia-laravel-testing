# Changelog

All notable changes to `claudiodekker/inertia-laravel-testing` will be documented in this file

## 2.0.0 - 2021-01-18
- Reworked the entire library using a brand-new, fluent syntax ([#18](https://github.com/claudiodekker/inertia-laravel-testing/pull/18))
- Updated deprecation notice: This version serves as the **official beta** for [inertiajs/inertia-laravel](https://github.com/inertiajs/inertia-laravel).
- `$response->assertInertia('component')` now also asserts that the component exists on the filesystem ([#17](https://github.com/claudiodekker/inertia-laravel-testing/pull/17))

## 1.1.0 - 2020-09-10
- Added the ability to select a single (nested) prop using `$response->inertiaProps('nested.prop')`

## 1.0.0 - 2020-09-03
- Initial release based on [this PR](https://github.com/inertiajs/inertia-laravel/pull/124)
