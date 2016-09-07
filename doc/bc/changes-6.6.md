# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes


## Deprecations


## Removed features

* All `ezpublish.api.service.*` services are now aliases, not reusing `ezpublish.api.reposiotry` alias.

  As part of `EZP-25679: Decouple SignalSlot services` all API services are now individual aliases,
  meaning they won't automatically take advantage of `ezpublish.api.reposiotry` to pick implementation.
  This is done to not have to load the whole repository and all it's services, just to load one.
