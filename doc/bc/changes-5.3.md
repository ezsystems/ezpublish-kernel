# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* New method `eZ\Publish\API\Repository\RoleService::deletePolicy` is introduced.

* Method `eZ\Publish\API\Repository\RoleService::removePolicy` will throw
  `eZ\Publish\API\Repository\Exceptions\InvalidArgumentException` in case when
  Policy does not belong to the given Role.

## Deprecations

* Method `eZ\Publish\API\Repository\RoleService::removePolicy` is deprecated in
  favor of new method `eZ\Publish\API\Repository\RoleService::deletePolicy`.

* Method `eZ\Publish\API\Repository\UserService::loadAnonymousUser` is deprecated
  in favor of using `eZ\Publish\API\Repository\UserService::loadUser`, passing
  anonymous user ID as argument.

No further changes are known in this release at the time of writing.
See online on your corresponding eZ Publish version for
updated list of known issues (missing features, breaks and errata).
