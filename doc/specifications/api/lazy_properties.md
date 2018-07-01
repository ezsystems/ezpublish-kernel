# Lazy object properties


As PHP API in eZ Platform is often used to populate templates via generic controllers, there is often a
need to provide possibility to easily get additional info relevant directly to the domain objects involved.

Prior to this feature this is only possible by:
- Write own full fledge controller losing out on many content features by the system
- Overriding the view controller where you can do almost anything you want, but you'll need to then maintain it yourself
  and resort to PHP for data which in some cases are pretty basic need for the template.
- Resort to query type which is limited to one set of objects, and mainly suitable to load for instance child objects.
- Use of third party solutions which in varying degree diverges from the official supported API.

While relevant for all domain objects, the most pressing need is for the eZ content model to get _for instance_:
- content info -> content type
- content info -> section
- content info -> main location _(just meta info\*, TBD if it should follow permission rules)_
- content info -> locations _(just meta info\*, should be filtered by permissions like API, then what about visibility?)_
- location -> content
- ...

However when doing so we need to take some technical consideration into account to avoid creating more problems than we
solve.

<small>\* _see technical consideration #3_</small>

### Technical consideration 1: Keep logic out of Value objects

In eZ Platform the objects involved here are [DDD Value objects](https://martinfowler.com/bliki/ValueObject.html), which
in itself contain as little logic as possible.

So one technical constraint here is that we should not end up with loading logic spread across
all value objects, and instead make sure to keep such logic in internal repository services
which can be unit tested more cleanly and more easily refactored later.


### Technical consideration 2: Avoid common performance pitfalls with bulk loading

Avoid common performance pitfalls found in other PHP applications:

#### eZ Publish legacy
Such functionality existed in eZ Publish "legacy", however it was often the culprit of performance
issues, as they were loading data O^n, leading to a massive amount of SQL calls to load data repeatedly.

Example:
```smarty
{foreach $nodes as $child_node}
      {$child_node.name} {* One raw sql call to get name(s) *}
      {$child_node.className} {* One fetch for object and one for content class *}
     {$child_node.object.owner.name} {* One fetch for owner (object feteched above) *}
{/foreach}
```

Inside the loop we have at least 4 sql calls going on, which will be done on each iteration, and while it could be
reduced to 3 per iteration by changing first line to `{$child_node.object.name}`,  many of the sql lookups could rather
have been done in bulk while still being lazy in such cases.


#### Doctrine ORM

Doctrine provides a range of ways to let you specify how to load reference(s), [from eager, to lazy and extra lazy](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/annotations-reference.html).

However when iterating entities and accessing a reference(s) property, or [when accessing properties not pre loaded](http://www.doctrine-project.org/2009/05/29/doctrine-lazy-loading.html),
there will be additional sql calls being made per entity just like in eZ Publish example above, with same outcome.


### Technical consideration 3: Avoid iteration of repository

Given the dynamic nature of lazy properties it's beneficial to take care when designing them to avoid ability to
traverse whole repository _(or the whole content structure)_ using them. As this is not a feature they are meant to
solve.

Anticipating bulk loading of lazy properties across collections as mentioned in #2 can only help so much against the
performance problems mentioned. To further avoid the situation lazy properties should also not allow traversing
the object graph beyond the [root aggregate](https://martinfowler.com/bliki/DDD_Aggregate.html).

E.g.
- Displaying paths of a location needs own cache tagging and as such is better served by dedicated view to serve it
  provided by the application .
- Showing site map is a dedicated problem that at some point need dedicated solution (own service).
- Listing children or listing content assigned to a section. This is better served using for instance query type to be
  able to specify filters, sorting & paging.

## Technical Requirements

Based on the context above, our requirements can be be defined as such:

- Allow to load given properties lazy.
- Also allow to set given property's object(s) upfront in case API already happens to have the data needed.
- Allow to load in bulk when applicable.

### Non goals

- Be able to iterate the whole repository
- Exposing collections that you would typically want to filter, sort and page.
- Cache the objects, this is responsibility of SPI Persistence Cache\*


<small>\* _Introducing API cache would require us to refactor Core/Repository quite a bit.
Lower hanging fruit would probably be to re-introduce a v2 SPI Persistence in-memory cache for meta data which don't
frequently change (types, sections, states, ..). This can for instance be done in `Core/Persistence/Cache` in
similar ttl based way as `CachedPermissionService` now does for permission lookups._</small>


## Design

As [researched in PR 2094](https://github.com/ezsystems/ezpublish-kernel/pull/2094) which was focusing on lazy loading
collections, using lazy collections and especially using PHP's generators directly would lead to BC breaks in current API.

To overcome these issues further attempts showed an opportunity to combine the following concepts:
- Proxy objects extending API value objects for use in plain arrays, as well as in singular cases
- Passing in vanilla Generator and id, load object on demand using `Generator->send($id)` for both bulk and singular use

Example:
```php
trait GeneratorProxyTrait
{
    // (properties ...)

    public function __construct(Generator $generator, mixed $id)
    {
        $this->generator = $generator;
        $this->id = $id;
    }

    public function __get($name)
    {
        if ($name === 'id') {
            return $this->id;
        }

        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->$name;
    }

    // (...)

    protected function loadObject()
    {
        $this->object = $this->generator->send($this->id);
        $this->generator->next();
        unset($this->generator);
    }
}

class ContentTypeGroupProxy extends APIContentTypeGroup
{
    use GeneratorProxyTrait;

    /**  @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup|null */
    protected $object;

    public function getNames()
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->getNames();
    }

    // (rest of methods ...)
}

class ContentTypeDomainMapper
{
    // (...)

    public function buildContentTypeGroupProxyList(array $ids, array $prioritizedLanguages = []) : array
    {
        $groups = [];
        $generator = $this->generatorForContentTypeGroupList($ids, $prioritizedLanguages);
        foreach ($ids as $id) {
            $groups[] = new ContentTypeGroupProxy($generator, $id);
        }

        return $groups;// to be used in for instance ContentType->contentTypeGroups
    }

    private function generatorForContentTypeGroupList(array $ids, array $prioritizedLanguages = []) : \Generator
    {
        $groups = $this->contentTypeHandler->loadGroups($ids);
        while (!empty($groups)) {
            $id = yield;
            yield $this->buildContentTypeGroupDomainObject(
                $groups[$id],
                $prioritizedLanguages
            );
            unset($groups[$id]);
        }
    }

    // (...)
}
```

This allows us to:
- use plain arrays plural object properties
- be able to pass in bulk loading generator into proxies along with their unique id
  - but also be able to use for singular objects without any adjustment to code
- take advantage of PHP's built-in async on demand nature of Generators.
- avoid putting logic in value objects, and avoid having to inject any heavy object like repository or similar.


Downsides:
- This is clearly misuse of Generators
  - However given this is kept as a implementation detail this can relatively easily be refactored later once the
    language provides other suitable features for executing code on demand.
- Subject to issues described in https://wiki.php.net/rfc/generators#closing_a_generator
  - However these are similar to issues you would experience with injecting repository object or similar into value
    object, which would probably be worse as it would imply explicit circular references.
