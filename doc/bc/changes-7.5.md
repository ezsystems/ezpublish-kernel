# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* Password quality checks are by default more strict than before.

  Passwords must be at least 10 characters long, and must include upper and lower case, and digits. This
  new default can be changed by editing the user account field type in the User content type.

  This applies to the standard User content type if you install eZ Platform from scratch, and to any new
  User content types you create after install/upgrade.

  It does not apply to the standard User content type if you upgrade from an earlier release. If you make
  this change in your existing User content type, existing users with shorter/simpler passwords will
  still be able to log in, but they will not be able to make new passwords that violate the requirements.

  Similarly, the default password of the Admin user is not changed. But you must of course change it
  before going live with a new project, and when you do, the new rules come into effect.

* Language Limitation supports now properly multilingual Content items allowing to modify translation
  which is not main for users with limitation to that translation only.

  Creating draft from existing Versions is no longer disallowed, even if a source Version does not
  contain any of the translations that are within the scope of the Language Limitations.
  This is due to the fact that when creating a Draft, an intent of translating is not known yet.

## Deprecations

* The `\EzSystems\PlatformInstallerBundle\Installer\CleanInstaller` class and its Service Container
  definition (`ezplatform.installer.clean_installer`) have been deprecated in favor of
  `EzSystems\PlatformInstallerBundle\Installer\CoreInstaller` which requires the
  [Doctrine Schema Bundle](https://github.com/ezsystems/doctrine-dbal-schema) to be enabled.

* The `ezplatform.installer.db_based_installer` Service Container definition has been deprecated in
  favor of its FQCN-named equivalent `EzSystems\PlatformInstallerBundle\Installer\DbBasedInstaller`.
