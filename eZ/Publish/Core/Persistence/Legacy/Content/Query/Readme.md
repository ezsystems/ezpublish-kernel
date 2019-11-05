# Persistence\Legacy\Content query handling


Contains common set of code for query handling in this Storage Engine.
Can be extended by means of implementing provided interfaces + configure as tagged service (@TODO:).

## Design & Usage

### Where this is intended to be used

- `TrashHandler::findTrashItems($criterion)`
- `LocationHandler::filterLocations($criterion)` _(Future)_
- `ContentHandler::filterContent($criterion)` _(Future)_


### Difference from Legacy Search Engine

The code here is only meant for persistence, for fetch/filtering use cases.

For performance reasons it:
- Is meant to use joins for extra tables, and not sub selects.
- Does not support FulltextCriterion _(this is not search)_.
- Does not support Field* Criteria _(until that can be done in a performing way that can be supported reliably)_.

For implementors it also differs by using Doctrine DBAL objects.

### Doctrine DBAL use

Each interface explicitly documents how to use provided Doctrine objects, and also what to expect already added in query
by default.

**WARNING**: Using or assuming anything else can break your code at any moment and is strongly discouraged, if something
             seems missing propose it as PR to help improve clarify definition of valid use.
