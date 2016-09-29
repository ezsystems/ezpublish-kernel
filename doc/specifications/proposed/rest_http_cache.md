# REST HTTP cache multi-tagging

## Current implementation

Controller actions that are meant to be cached will wrap the returned value object in a CachedValue object. With the HTTP
cache improvements, a second parameter is added that sets the cache tags:

```
return new CachedValue($content, [‘location’ => $content->mainLocationId]);
```

This approach might place the responsibility of HTTP caching in the wrong layer.

Furthermore, since controllers are used by rest embedding to load values, several controllers might return a CachedValue.
Does it imply that an embedded value will be able to turn caching on for a whole response ? It might very well do...
Would we need a MASTER/SUB thing ? Where sub-visits would not be able to decide if caching is enabled on the response or not ?
Overall, the fact is that each visited object must be able to tag the response depending on what it contains.

## Improved implementations

### Decorated controllers

A container extension wraps the REST controllers into a caching controller. This controller conditionally
wraps the returned value object into a CachedValue one.

How would this work with embed value loading ? Loading might then use the cached version. Does it have a negative impact ?

**We should also change `CachedValue` to `CacheableValue`**. After all, the controller doesn't say that the value **must** be
cached, but that it can be.

### View listener

A view listener, that runs after the one that visits value objects into responses.
How would the listener gather/figure out which values that were visited by embedding ?

~Or a view listener that runs _before_ the one that visits value objects into responses: it would use the object returned
by the controller to set the cache tags. Could it figure out what to do with embedded objects ? Since embedding happens
_during_ the visit phase, it can't know it unless it duplicates the logic from the embed system.~

### FosHttpCacheBundle listener ?
FOS HTTP Cache Bundle has its own listener.

It supports multiple sources of cache rules:
- configuration (by path)
- annotations
- code in controllers

## Cache triggering
How do we decide what to cache / not to cache ?

By Value type ?
By REST route ?
By configuration ? Does it make it easier to see at a glance what is cached and how ?

## Side questions
- Could we like 'collect' what API values are loaded by a cachable operation ?
  e.g. if 3 locations are loaded to build a content view, they're probably involved in the view rendering, and could
  be used to tag the response.
  After all, we do the same thing (with signals) to purge cache (by tags).

## Influence of a `CacheableValue` from an embedding perspective

Embedded objects are loaded using their route's controller. Most controllers will return a `CacheableValue`. How does
this influence the response ?

Each embedded object may add its own cache tags to the response. This is precisely what we are looking for: if the tagging
logic happens in the controller, we _depend_ on `CacheableValue` objects.

Do we want to handle caching in a different layer (e.g. not in each controller) ? If we don't, no problem, we can use
the same caching logic when visiting embedded objects. Are there benefits in separating it ?

- Centralisation of caching strategy, can be inspected / made dynamic ?
- Better separation of concerns: controllers don't need to care about caching at all
- Unit testable

Okay, it looks like we don't want to rely on the controller here. Remaining options are _controller decorator_ and
_a view listener_. The View Listener might actually have to be ruled out, at least without extra work: before value
visiting has happened, we only have the value, without the embedded objects. After visiting, we only have the generator,
with the abstract output tree (unless we parse the output, but let's not go there).

The controller decorator could work: the value returned by the real controller would conditionally be wrapped into
a CacheableValue, depending on caching rules. Those could be similar to what FOSRestBundle does with the expression language,
as the value has all the values it needs for caching (see HTTP cache purge signal slots).

**Bonus:** We could also integrate FosHttpCacheBundle, and use its listener. Tags added to the `_tag` request attribute
will be added to the response by the fos listener. In that case, it would make sense to use it in the SignalSlots layer
as well (would they even work together ? Yes they would, existing tags aren't overwritten). It doesn't really limit us,
and delegates handling of tags to another layer.

But wait wait. What do we do with our CachedValue objects again ? They're visited as soon as we get them, and we don't
have an event handler for each of them: the controller method is called directly. Does it mean that the `ResourceLink`
visitor has to be responsible for caching ? That's tough... well, it could use whatever `ValueTransformer` we would use
in a listener, but inside the visitor. Not _that_ bad, is it ?

But do we have to distinguish "master" and "sub" values ? e.g. may an embedded value do anything to the response that
the master one does ? Set the TTL, enable caching altogether... The risk is that a response that isn't meant to be cached
(a list of things ?) embeds values that are cacheable. We only need the tags, _not_ any of the other global caching properties.

Using different ValueTransformers could be viable. Creation of the cached object can be done in an abstract/protected
property, that is implemented/overridden by the different transformers. The ResourceLink visitor would use a different 
value transformer, that returns a `TaggedValue`. The event listener would return a `CacheableValue`. 
`CacheableValue` implements `TaggedValue`:

```
interface TaggedValue
{
    public function getTags(): array;
}

interface CacheableValue
{
}

class RestTaggedValue implements TaggedValue
{

class RestCacheableValue extends RestTaggedValue implements CacheableValue, TaggedValue
{
}
}
```

That way, one visitor can take care of `TaggedValue` objects, and set tags only, while another one can set tags _and_
enable caching altogether.

Isn't there a better method than two classes ? Is it really necessary to do better ?

Both the visitor and the event listener need a service that conditionally transforms a given value. It could be a `ValueTransformer`,
as a matter of fact. It would uses a set of rules and matching `ValueTransformers` to transform values based on their type:
`TypeValueTransformer` ?

```php 
interface TypeValueTransformer
{
  public function transform($value);
}

class CacheValueTransformer implements TypeValueTransformer
{
  public function transform($value)
  {
    $tags = $this->cacheTagger->getTags($value);

    return new CachedValue($value, $tags)
  }
}
```

### Tagging of values

A ValueCache filter that uses a map of type => expressions to...

How do we _enable_ caching on a value type again ?
Annotations on value object classes would be f'ing convenient...

```
 **
 * @EnableCaching
 * @CacheTag @="content-" ~ value.id
 * @CacheTag @="content-type-" ~ value.contentInfo.contentTypeId
 *
class Content {}
```

Meanwhile, we could use a simple mapping:
```
parameters:
    'eZ\Publish\API\Repository\Values\Content':
```

Or...

What about One Tagger per Tag ? Each could have a 'canTag' method, with a (configurable) list of classes.
To actually tag stuff, all the taggers would be called on the value, and each tagger would add its own tag(s).

Don't we have something similar with our SignalSlots ?
We already have stuff like PublishVersionSlot. Those HttpCache slots return a list of tags given a slot with a value.

We could have a ViewRestValue signal, and one slot per tag. Or not, per value, it scales better.
it would do the exact same thing,
but when a REST value is viewed. Slots would check both the signal type and the value type in their accept method.

If acceptable, they would generate the tags array.

But actually nevermind the existing HttpCache slots: they're cache _purging_ slots. They can't be used as is for
tagging. Or else, we need a completely different type of Slot. Is it worth using them, knowing that they're
meant to be stateless, and don't expect value objects to be part of the signals.

Can we think of something that easily combines tags types and taggable values ?

|type|content-<content-id>|content-type-<content-type-id>|location-<locationId>|parent-<parent-location-id>|path-<path-string>|relation-<relation-content-id>|
|----|--------------------|------------------------------|---------------------|---------------------------|------------------|------------------------------|
|Content|`content.id`|`content.contentInfo.contentTypeId`|`locations(content)[].id`|`locations(content)[].parentLocationId`|`locations(content)[].pathString`|`relations(content).id`|
|Location|`location.contentInfo.id`|`location.contentInfo.contentTypeId`|`location.id`|`location.parentLocationId`|`location.pathString`|
|Content-Type|n/a|`value.id`|n/a|n/a|n/a|
|Other ?|

### Summary: simple approach

#### Outline
- Controllers are wrapped with an decorator that handles caching: based on the value object returned by the parent method,
  each decorating action may wrap it inside a CachedValue object, and tag the said object with whatever tags it requires.
- The `CachedValue` contains the cache tags for the loaded value object.
- When visiting a loaded object, the ResourceLink will use special handling for CachedValue objects:
  It adds to the Response any cache tag from the response
  It unwraps the wrapped value from the CachedValue, and visits it instead.

#### Story
For each REST controller, there is an `HttpCacheController`. Those are decorators,
that pass actions to an inner, real controller. They will, for actions where it applies,
wrap the value returned by the inner controller into a `CachedValue`, with
the applicable cache tags.

Given that it embeds values into the result, the `ResourceLink` ValueObjectVisitor is responsible for
tagging the answer it is part of

### Summary
`CachedValue` objects are no longer returned by their respective controller. They are instead generated
through some event system.
