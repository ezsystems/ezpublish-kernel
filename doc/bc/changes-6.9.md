# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes
- EZP-25679: `ezpublish.api.service.*` now correctly point to the outermost api: SignalSlot

  What changed: All `ezpublish.api.service.*` services are now aliases, not reusing `ezpublish.api.reposiotry` alias to better decouple the repository and correct the services returned.

  How it might affect your code: Make sure you always type hint against the interfaces for our `API` and never `Core` implementation classes to avoid this causing issues.

## Deprecations

## Removed features
