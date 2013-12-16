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

* Value of `eZ\Publish\API\Repository\Values\Content\VersionInfo::STATUS_ARCHIVED` constant
  is changed form 2 to 3. This brings it in align with status constants defined in SPI and
  stored in database. The change was done with introduction of Status Limitation, in order
  to avoid unnecessary translation between the two layers. It will cause BC problems only
  if hardcoded values were used instead of provided constants.

* LanguageCode criterion `eZ\Publish\API\Repository\Values\Content\Query\Criterion` has new
  optional parameter `$matchAlwaysAvailable`, defaulting to `false`. With this parameter it
  is possible to match Content that has no translations in languages passed to the criterion,
  but is always available.

* Exception `eZ\Publish\API\Repository\Exceptions\LimitationValidationException` is added,
  thrown in `RoleService` methods creating and updating Limitations, when given Limitation is
  not valid. These are: `createRole`, `addPolicy`, `updatePolicy`, `assignRoleToUserGroup`
  and `assignRoleToUser`.

* Exception `eZ\Publish\API\Repository\Exceptions\InvalidArgumentException` is documented on
  `RoleService` methods `addPolicy` and `updatePolicy`, thrown when Limitation of the same
  type is repeated in policy create or update struct or if Limitation is not allowed on
  module/function. This only documents existing behaviour, therefore it is only documentation
  change.

* `eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field` and `eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget`
  have a new required parameter: $languageCode. This is introduced because sorting on a Field value
  of Content that exists in multiple languages is ambiguous and was producing wrong results.

## Deprecations

* It was incidentally possible to reference resources in REST API payloads without
  the REST prefix. This is no longer possible, and will throw an exception, as expected.

* Binary FieldTypes (Image, BinaryFile, Media) Value

  As explained in API, the 'path' property for those classes is deprecated,
  and 'id' should be used instead.

* PageController::viewBlock()

  `pageService` injection will be removed in v6.0. See https://jira.ez.no/browse/EZP-21786.

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
