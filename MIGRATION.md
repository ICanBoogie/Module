# Migration

## v5.x to v6.x

### New Requirements

None

### New features

None

### Backward Incompatible Changes

- Removed all bindings to `ControllerAbstract` since it doesn't extend `Prototyped` anymore.

### Deprecated Features

- Models are no longer maintained by modules, they need to be defined with `activerecord` config
  fragments. `Description::MODELS` now only hold model identifiers.

### Other Changes

None
