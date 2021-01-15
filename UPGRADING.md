## Upgrading from 1.x to 2.x

For most users, upgrading to v2.x only means updating your composer dependency, with no need to actually apply any 
code-level changes. However, in some rare situations it might be that a few minor changes do need to be applied.

Previously, when using `$response->assertInertia('componentName')`, this package would only look at the Inertia-rendered
response and compare it to what is being asserted. However, as of v2.x, it will also ensure that the `componentName` 
component _file_ actually exists.

If this causes errors in your codebase, or if you'd rather have this check disabled, you can do so by changing the
config settings that this package uses. To obtain a (modifiable) copy of this config file, you can run the following 
artisan command:

```bash
php artisan vendor:publish --provider="ClaudioDekker\Inertia\InertiaTestingServiceProvider"
```
