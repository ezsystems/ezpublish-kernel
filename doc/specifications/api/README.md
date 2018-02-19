# API

API in eZ context refers to its Public API's. This folder covers the PHP API specifically.
Specifications for REST API can be found in `doc/specifications/rest` folder.

Common for them both is the Public API BC promise and the underlying modeling of the eZ Content Repository.

### PHP API

PHP API _(aka papi)_ is referring to interfaces found `eZ\Publish\API`, currently
covering all eZ Content Repository functionality, this is where you'll find its living specification.

This folder is mainly covering concepts and features of the implementation of the API, found in:
- `eZ\Publish\Core\Repository`: Implementation of business logic of the Repository.
- `eZ\Publish\Core\SignalSlot`: "Signal Slot" implementation of Repository allowing to use slots (listener) for signals
  (events) on every call to api that changes data in the repository.

