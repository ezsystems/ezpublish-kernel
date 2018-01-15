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


The point of this is to secure a stable foundation for everyone to build upon for all provided Public API's.
