# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* Published date of the content behaviour has been changed to reflect the time of the publishing of the first version.
  Before, if the publishedDate was not set manually, it was being set to the publishing time of the latest version.


## Deprecations

* EzSystems\PlatformInstallerBundle\Installer\Installer::createConfiguration()

  As part of EZP-25369 this method has been deprecated an is not in use anymore. Installers are now not allowed to
  generate system config. Configuration & code generation will be kept separated from database and content installation
  going forward.

* Passing an `int` argument to the `HostElement` and `URIElement` SiteAccess Matchers constructor is deprecated
  and will be removed in the future, a single-element array ( 'value' => $number ) should be used instead.


## Removed features

