# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.


## Changes

* When using a twig pagelayout to execute a legacy view (possibility given since 5.1),
  the legacy pagelayout is not executed anymore.
  This brings speed improvements, but might have unintended consequences if the
  legacy pagelayout templates were improperly used to carry out tasks with side
  effects (logging, calling webservices, etc)

## Deprecations

* It was incidentally possible to reference resources in REST API payloads without
  the REST prefix. This is no longer possible, and will throw an exception, as expected.

## Removals


No further changes known in this release at time of writing.
See online on your corresponding eZ Publish version for
updated list of known issues (missing features, breaks and errata).
