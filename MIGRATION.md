# Migration

## v5.x to v6.x

### New Requirements

- PHP 8.1+

### New features

- Added a configuration builder for module descriptors.
- Added `ModuleProvider` interface, implemented by `ModuleProvider\Container`. Modules are now loaded from the dependency-injection container.

### Backward Incompatible Changes

- Modules are now defined using configuration fragments instead of `descriptor.php` files.
- Module descriptors are now instances of `Descriptor` instead of arrays.
- Renamed `Descriptor::INHERIT` as `Descriptor::$parent`.
- Removed `Descriptor::NS`.
- Models are no longer maintained by modules, they need to be defined with `activerecord` config
  fragments. `Descriptor::$models` now only hold model identifiers.
- Removed all bindings to `ControllerAbstract` since it doesn't extend `Prototyped` anymore.
- Removed prototype method `Application::get_modules`.
- Renamed `ModuleCollectionInstallFailed` as `ModuleInstallFailed`.
- Moved module installation from `ModuleCollection` to `BasicModuleInstaller`.

### Deprecated Features

None

### Other Changes

None
