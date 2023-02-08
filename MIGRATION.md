# Migration

## v5.x to v6.x

### New Requirements

None

### New features

- Added a configuration builder for modules.
- Added `Descriptor::ANCESTORS`.

### Backward Incompatible Changes

- Modules are now defined using configuration fragments instead of `descriptor.php` files.
- Renamed `Descriptor::INHERIT` as `PARENT`.
- Removed `Descriptor::NS`.
- Models are no longer maintained by modules, they need to be defined with `activerecord` config
  fragments. `Descriptor::MODELS` now only hold model identifiers.
- Removed all bindings to `ControllerAbstract` since it doesn't extend `Prototyped` anymore.
- Removed prototype method `Application::get_modules`.

### Deprecated Features

None.

### Other Changes

None
