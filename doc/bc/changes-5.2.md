# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* When using a twig pagelayout to execute a legacy view (possibility given since 5.1),
  the legacy pagelayout is not executed anymore.
  This brings speed improvements, but might have unintended consequences if the
  legacy pagelayout templates were improperly used to carry out tasks with side
  effects (logging, calling webservices, etc)

* In the REST API JSON responses, the boolean properties are now real boolean
  values instead of the string "true" or "false".

* Symfony routes are not interpreted any more with `legacy_mode: true` (see [EZP-21628](https://jira.ez.no/browse/EZP-21628)).
  However, it is possible to define routes that can be interpreted anyway:

  ```yaml
  ezpublish:
      router:
          default_router:
              # Routes that are allowed when legacy_mode is true.
              # Must be routes identifiers (e.g. "my_route_name").
              # Can be a prefix, so that all routes beginning with given prefix will be taken into account.
              legacy_aware_routes: [my_route_name, my_route_prefix_]
  ```

## Deprecations

* It was incidentally possible to reference resources in REST API payloads without
  the REST prefix. This is no longer possible, and will throw an exception, as expected.

* Binary FieldTypes (Image, BinaryFile, Media) Value

  As explained in API, the 'path' property for those classes is deprecated,
  and 'id' should be used instead.

## Removals

### API

* Binary FieldTypes (Image, BinaryFile, Media) Value

  The path property was renamed to id (see EZP-20948)
  Path remains available for both input and output, as a BC facilitator,
  but will be removed in a further major version and should not be used

* ContentTypeService->createContentType()

  The ContentTypeValidationException is introduced on this method, thrown when
  ContentType being created is not valid. This will be the case when multiple field
  definitions of the same singular type are provided in the create struct.

* ContentTypeService->addFieldDefinition()

  ContentTypeFieldDefinitionValidationException is added to the interface of this
  method. This exception was already being used, therefore this is only a PHPDoc change.

  BadStateException is added to this method, thrown when content type that is being
  updated is in a bad state. There are currently two possible cases when this can
  happen:

  * When the field definition is of singular type already existing in the content type.
  * When the field definition is of the type that can't be added to a content type that
    already has content instances is being added to such content type.

No further changes known in this release at time of writing.
See online on your corresponding eZ Publish version for
updated list of known issues (missing features, breaks and errata).
