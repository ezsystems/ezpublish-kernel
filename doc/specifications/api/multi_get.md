# Repository Multi Get

Since the introduction of the Repository API a few things have been missing in terms of having a consistent way to load
several repository entities at once, & on a related note being able to opt out of the strict catch/throw design of
singular load methods.

The use cases are many, but the recurring pattern is when one list of id's needs to be looked up in one go to avoid
having O(n) code spreading all over the architecture from bottom to top, as opposed to closer to the ideal O(1) lookups.

So that is what this specification is for, defining a pattern to use to expose API endpoints to load multiple entities
at once, by ids/identifiers/remote-ids

#### Note on Caching
Part of the reason why this is being proposed now is that with kernel version 7.0 _(eZ Platform v2)_ and it's move to
Symfony Cache. We can now actually load several cache items in one call, meaning any exposure of multi load
endpoints will provide noticeable performance improvements also when cache is warm, not just when it is cold & request
goes to database.

## SPI _(PHP)_
Before we try to define how a multi lookup API endpoint should look like, we'll first cover the layer below, the _Service
Provider Interface_, or *SPI* for short.

Reason for that is that the concerns are simpler. SPI layer does not deal with Business layer logic and in general only
knows about two things:
- Returning entity
- Throwing when entity is _Not Found_

However as written in intro, there are cases where this throw logic is unwanted. And more importantly when loading several
entities, if we throw when one is missing, we are actively refusing the callee to retrieve the items that *where* found.

#### Design

As such, for the lower layer SPI the following pattern would be fitting, here using `ContentInfo` as an example:
```php
    /**
     * Return list of unique Content Info, with content id as key.
     *
     * Missing items (NotFound) will be missing from the array and not cause an exception, it's up
     * to calling logic to determine if this should cause exception or not.
     *
     * @param array $contentIds
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo[<mixed>] An iterable set of ContentInfo where id is key.
     */
    public function loadContentInfoList(array $contentIds): iterable;
```

Notes:
- If the endpoint is retuning arrays, generators or collection object is up to each and every use case. Only thing
  defined here is that it is _iterable_.
- There is no offset or limit, given we do lookup on id's that would be up to callee.
- Callee can easily get missing entities with `$missingIds = array_diff($contentIds, array_keys($contentInfoList))`

##### Note on more complex cases: LoadStructs

For loading whole Content we also need to at least specify which language each and every item should be loaded in, in
order to take always available into account, and in order for cache layer to know enough to generate unique cache keys.

To do this, we can use a LoadStruct, see current _(as of 2.2)_ SPI design for this:
https://github.com/ezsystems/ezpublish-kernel/blob/master/eZ/Publish/SPI/Persistence/Content/LoadStruct.php


## API _(PHP & REST)_

With API layer we also need to take into account business logic, permissions mainly. Specifically attempts to load
entities current user is _Unauthorized_ to load _(read)_.

So here we have three concerns:
- Returning entity
- Throwing when entity is _Not Found_
- Throwing when user is _Unauthorized_ to read the given entity

But again there are plenty of use cases where throw/catch logic is not wanted. E.g. one being in Studio Landing pages,
specifically blocks where multiple content items have been assigned to be displayed, and we want to show what the user
has access to, ignoring the rest.

#### PHP Design

For the higher layer API we would need something more concrete, and with stronger type hinting as this is among the
strengths of the API. Taking Content as an example here we can envision the following pattern:
```php
    /**
     * Return collection of unique Content Info, with content id as key.
     *
     * @param array $contentIds
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[<int>]|\eZ\Publish\API\Repository\Values\ContentCollection
     */
    public function loadContentCollection(array $contentIds, array $prioritizedLanguages = []) : ContentCollection;


Interface ContentCollection implements iterable
{
    /**
     * Return all content objeects, also the once user does not have access to.
     *
     * This method is only to be used in cases where you know for sure you want to load items
     * regardless of permissions.
     *
     * To get list filtered by permissing, just iterate ContentCollection itself.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[<int>]
     */
    public function all(): array;

    public function getNotFoundIds() : array;

    public function getUnauthorizedIds() : array;
}
```

Notes:
- Like in SPI we only document what is returned is _iterable_, but in this case also that it implements an interface to
  get info on items not part of the collection _(NotFound or Unauthorized)_
- Also like in SPI there is no offset or limit, given we do lookup on id's that would still be up to callee here.

#### REST Design

_TBD:_ See possible aproach for content loading here:
https://gist.github.com/andrerom/d0c8cc9c1db75d2617e2942e5bb6e5ee
