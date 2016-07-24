# REST resource embedding

Rest embedding allows an API consumer to request references from the
response to be embedded, in order to avoid extra REST calls.

## Example

A location response contains a reference to the content item's main location:

```
curl -X GET http://localhost:8000/api/ezp/v2/content/objects/1
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Content media-type="application/vnd.ez.api.ContentInfo+xml" href="/api/ezp/v2/content/objects/1" remoteId="9459d3c29e15006e45197295722c7ade" id="1">
 <ContentType media-type="application/vnd.ez.api.ContentType+xml" href="/api/ezp/v2/content/types/1"/>
 <Name>eZ Platform</Name>
 <Versions media-type="application/vnd.ez.api.VersionList+xml" href="/api/ezp/v2/content/objects/1/versions"/>
 <CurrentVersion media-type="application/vnd.ez.api.Version+xml" href="/api/ezp/v2/content/objects/1/currentversion"/>
 <Section media-type="application/vnd.ez.api.Section+xml" href="/api/ezp/v2/content/sections/1"/>
 <MainLocation media-type="application/vnd.ez.api.Location+xml" href="/api/ezp/v2/content/locations/1/2"/>
 <Locations media-type="application/vnd.ez.api.LocationList+xml" href="/api/ezp/v2/content/objects/1/locations"/>
 <Owner media-type="application/vnd.ez.api.User+xml" href="/api/ezp/v2/user/users/14"/>
 <lastModificationDate>2015-11-30T13:10:46+00:00</lastModificationDate>
 <publishedDate>2015-11-30T13:10:46+00:00</publishedDate>
 <mainLanguageCode>eng-GB</mainLanguageCode>
 <currentVersionNo>9</currentVersionNo>
 <alwaysAvailable>true</alwaysAvailable>
 <ObjectStates media-type="application/vnd.ez.api.ContentObjectStates+xml" href="/api/ezp/v2/content/objects/1/objectstates"/>
</Content>
```

By adding an `X-eZ-Embed-Value` header to the request, we can get the
main location object embedded into the response:

```
curl -X GET http://localhost:8000/api/ezp/v2/content/objects/1 -H 'x-ez-embed-value: Content.MainLocation'
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Content media-type="application/vnd.ez.api.ContentInfo+xml" href="/api/ezp/v2/content/objects/1" remoteId="9459d3c29e15006e45197295722c7ade" id="1">
 <ContentType media-type="application/vnd.ez.api.ContentType+xml" href="/api/ezp/v2/content/types/1"/>
 <Name>eZ Platform</Name>
 <Versions media-type="application/vnd.ez.api.VersionList+xml" href="/api/ezp/v2/content/objects/1/versions"/>
 <CurrentVersion media-type="application/vnd.ez.api.Version+xml" href="/api/ezp/v2/content/objects/1/currentversion"/>
 <Section media-type="application/vnd.ez.api.Section+xml" href="/api/ezp/v2/content/sections/1"/>
 <MainLocation media-type="application/vnd.ez.api.Location+xml" href="/api/ezp/v2/content/locations/1/2">
  <id>2</id>
  <priority>0</priority>
  <hidden>false</hidden>
  <invisible>false</invisible>
  <ParentLocation media-type="application/vnd.ez.api.Location+xml" href="/api/ezp/v2/content/locations/1"/>
  <pathString>/1/2/</pathString>
  <depth>1</depth>
  <childCount>8</childCount>
  <remoteId>f3e90596361e31d496d4026eb624c983</remoteId>
  <Children media-type="application/vnd.ez.api.LocationList+xml" href="/api/ezp/v2/content/locations/1/2/children"/>
  <Content media-type="application/vnd.ez.api.Content+xml" href="/api/ezp/v2/content/objects/1"/>
  <sortField>PRIORITY</sortField>
  <sortOrder>ASC</sortOrder>
  <UrlAliases media-type="application/vnd.ez.api.UrlAliasRefList+xml" href="/api/ezp/v2/content/locations/1/2/urlaliases"/>
  <ContentInfo media-type="application/vnd.ez.api.ContentInfo+xml" href="/api/ezp/v2/content/objects/1"/>
 </MainLocation>
 <Locations media-type="application/vnd.ez.api.LocationList+xml" href="/api/ezp/v2/content/objects/1/locations"/>
 <Owner media-type="application/vnd.ez.api.User+xml" href="/api/ezp/v2/user/users/14"/>
 <lastModificationDate>2015-11-30T13:10:46+00:00</lastModificationDate>
 <publishedDate>2015-11-30T13:10:46+00:00</publishedDate>
 <mainLanguageCode>eng-GB</mainLanguageCode>
 <currentVersionNo>9</currentVersionNo>
 <alwaysAvailable>true</alwaysAvailable>
 <ObjectStates media-type="application/vnd.ez.api.ContentObjectStates+xml" href="/api/ezp/v2/content/objects/1/objectstates"/>
</Content>
```

### The `X-eZ-Embed-Value` request header

Which resources must be embedded is specified using this request header.
It accepts several resources separated by commas:

`X-eZ-Embed-Value: Content.MainLocation,Content.Owner.Groups`

A resource is referenced by its "path" from the root of the response.
Resources from an embedded resource can also be embedded:

- `Content.MainLocation`
- `Content.ContentType`
- `Content.Owner.Groups`

### Permissions
If the user doesn't have the required permissions to load an embedded
object, the response will be untouched, and no error will be thrown.

### HTTP Caching
Responses will vary based on the embedded responses.

Thanks to HTTP cache multi-tagging, customized responses will expire as
expected: each embedded object will tag the response with the HTTP cache
tags it requires.

### Implementation

#### Resource links generation in value object visitors
Value object visitors don't use the router directly anymore to generate
links to resources. Instead, they build and visit a `ResourceRouteReference`
object with the name of the route and the route's parameters:

```php
class LocationValueObjectVisitor extends ValueObjectVisitor
{
  public function visit($generator, $visitor, $location)
  {
    // ...
    
    $generator->startObjectElement$generator->startObjectElement('Content');
    $visitor->visitValueObject(
      new ResourceRouteReference(
        'ezpublish_rest_loadContent',
        ['contentId' => $location->contentInfo->contentId]
    );
    $generator->endObjectElement('Content');
  }
}
```

#### The `ResourceRouteReference` value object visitor
This object's visitor extends the `RestResourceLink` visitor.

It uses the router to generate a link based on the `RestResourceLink`
properties, and invokes the parent's `visit()` method.

#### The `RestResourceLink` value object visitor
It first generate an `href` attribute, respecting the REST output that
existed before this feature.

It then uses a `PathExpansionChecker` to test if the current generator path,
returned by the `Generator::getStackPath()` method, is requested for expansion.
A `RequestHeaderPathExpansionChecker` uses the request to test if expansion
is needed.

If it is, a `ValueReferenceLoader` loads the referenced
value object. The returned value object it is visited and added to generated
output, inside the current object element.

#### The `ExpansionGenerator`
The `RestResourceLink` visitor passes an `ExpansionGenerator` when visiting
the loaded value object.

This OutputGenerator decorates the actual (XML or JSON) generator. It will
skip the first objectElement and its attributes generated for the embedded
object, in order to avoid duplicate nodes. In the example above, the `LocationList`
object is skipped:

```
<?xml version="1.0" encoding="UTF-8"?>
<Location media-type="application/vnd.ez.api.Location+xml" href="/api/ezp/v2/content/locations/1/2">
 <!-- ... -->
 <Children media-type="application/vnd.ez.api.LocationList+xml" href="/api/ezp/v2/content/locations/1/2/children">
  <!-- This is skipped -->
  <LocationList media-type="application/vnd.ez.api.LocationList+xml" href="/api/ezp/v2/content/locations/1/2/children">
   <Location media-type="application/vnd.ez.api.Location+xml" href="/api/ezp/v2/content/locations/1/2/55"/>
  </LocationList>
 </Children>
</Location>
```

### Loading of references
References are loaded by the `ControllerUriValueLoader`. Given a REST
resource URI (`/api/ezp/v2/content/objects/1`), it will determine and call
the REST controller action for that URI.

This implementation ensures that any REST resource that has a controller
can be embedded without requiring any extra development.

Resources that have multiple representations, such as Content/ContentInfo,
will use the optional media-type from the RestResourceReference to embed
the expected representation.

### HTTP caching
- Requires HTTP cache multi-tagging
- Response must vary on `x-ez-embed-value`
- Response must be tagged with all of the included items
  Since the controllers are used to expand objects, the required cache
  headers should be included automatically (to check)
