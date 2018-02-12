# API

API in eZ context refers to its Public API's, this folder covering the PHP API, and specifications for
REST API can be found in `doc/specifications/rest` folder.

Common for them both is the Public API BC promise and the underlying modeling of the eZ Content Repository.

### PHP API

PHP API _(aka papi)_ is referring to interfaces found `eZ\Publish\API`, currently
covering all eZ Content Repository functionality, this is where you'll find it's living specification.

This folder is mainly covering concepts and features of the implementation of the API, found in:
- `eZ\Publish\Core\Repository`: Implementation of business logic of the Repository.
- `eZ\Publish\Core\SignalSlot`: "Signal Slot" implementation of Repository allowing to use slots (listener) for signals
  (events) on every call to api that changes data in the repository.


### "Public API" BC promise
Public API signifies a BC promise beyond what is provided by [Semantic Versioning](https://semver.org/)
which is followed for interfaces and public services in all of today's eZ applications. The BC
promise is across a major version for smaller changes, and across several major versions in
case of major changes.

Example:

    For API features deprecated in 6.x series, they can earliest be removed in v8.0.0. In
    contrast, under Semantic Versioning it could have been removed in 7.0.0.

Exceptions:
- Major releases: Taking advantage of improvements in PHP, like native type hinting. This does not need prior
  deprecation as long as type is same or similar (e.g. array vs iterable vs generics) as previously documented.
- As API has BC promise for consumption, and official extension point is via SPI layer:
-- Patch releases: Extending the API _implementation_ can break as implementation changes.
-- Feature releases: Implementation the API interfaces is possible, however optional arguments or new methods might be
   added as long as it is possible to have implementation working across new and prior releases at the same time.
- Patch releases: Smaller bug fixes, where doc and implementation has been inconsistent or wrong.


The point of this is to secure a stable foundation for everyone to build upon for all provided Public API's.


### SPI BC promise

As of 7.0, SPI _(Service Provider Interface)_, being the official way to extend the Repository, follows the same
extended BC rules as Symfony Framework is following:
http://symfony.com/doc/current/contributing/code/bc.html

Example:

    This means when we need to add or change methods to SPI in patch or feature releases, we need to add new interfaces,
    and in next major (if needed) deprecated the introduced interface and move over to main interface.

This covers interfaces and classes in `eZ\Publish\SPI\*` namespace, and not implementation classes found in
`eZ\Publish\Core\*` or other namespaces.
