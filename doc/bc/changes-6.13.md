# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

## Deprecations

- The `eZ\Publish\API\Repository\ContentService::removeTranslation` method is deprecated and will be removed in 7.0.

  Use `eZ\Publish\API\Repository\ContentService::deleteTranslation` instead.


- The following password hash types are deprecated and will be removed in a future version:
  `eZ\Publish\API\Repository\Values\User::PASSWORD_HASH_MD5_PASSWORD`
  `eZ\Publish\API\Repository\Values\User::PASSWORD_HASH_MD5_USER`
  `eZ\Publish\API\Repository\Values\User::PASSWORD_HASH_MD5_SITE`
  `eZ\Publish\API\Repository\Values\User::PASSWORD_HASH_PLAINTEXT`

  Use one of the following types instead:
  `eZ\Publish\API\Repository\Values\User::PASSWORD_HASH_BCRYPT`
  `eZ\Publish\API\Repository\Values\User::PASSWORD_HASH_PHP_DEFAULT`

  The password hashes of existing users will be updated automatically to the new default hash type
  `eZ\Publish\API\Repository\Values\User::PASSWORD_HASH_PHP_DEFAULT`
  when they login or change their passwords, unless you have specifically configured
  `eZ\Publish\Core\Repository\UserService` to use one of the deprecated types.

- Zeta database abstraction layer is deprecated, usage of Doctrine DBAL is adviced instead.

  As has been possible since 6.11 (see changes-6.11.md) it is not possible to use Doctrine DBAL
  instead in all places, also field types. So this change deprecated the whole abstraction layer
  Zeta Database in favour of signaling the move to Doctrine DBAL which is easier to work with,
  and is more powerful so allows for making faster queries in advance cases.
  
  This affect:
  - `eZ\Publish\Core\Persistence\Database\*` Zeta DB abstraction
  - `eZ\Publish\Core\Persistence\Doctrine\*` Implementation of that abstraction for Doctrine


## Removed features
