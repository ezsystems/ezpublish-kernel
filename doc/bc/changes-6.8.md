# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

## Deprecations

* Most HTTP cache related elements from the kernel are deprecated in favour of the ezsystems/ezplatform-http-cache package

  This mostly includes the contents of the eZ/Publish/Core/MVC/Symfony/Cache/Http directory.
  Nothing is removed from this version and deprecation messages will not be logged.
  When installed and enabled, the newly introduced package will replace the services from the kernel with its own.
