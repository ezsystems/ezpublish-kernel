## REST embedding specification

Repository value objects from any REST response can be expanded/embedded
into the response, by means of custom request parameters.

### Implementation

In order to allow this, generation of links to other value objects gets
a new, custom API.

A custom value object is added, `LoadableValueObjectReference`. It is
meant to be created and visited by other value object visitors. It contains
the name of the value object type to load, as well as parameters for it:

```php
class LocationValueObjectVisitor extends ValueObjectVisitor
{
  public function visit($generator, $visitor, $location)
  {
    $generator->startObjectElement('Content');
    $visitor->visitValueObject(
      new ValueObjectReference(
        'Content',
        ['contentId' => $location->contentInfo->contentId]
    );
    $generator->endObjectElement('Content');
  }
}
```

It generates the link to the referenced value object, and add it to the
generator as an href attribute (the current behaviour of all visitors).

It uses a `valueReferenceLoader` to load the referenced
value object. If one is loaded, it is visited and added to generated output,
inside the current objet element.

A `RequestOutputGeneratorValueReferenceLoader` (gasp) uses the request
properties, as well as the Output Generator, determine if the referenced
value should be embedded or not.

Generation of value object links is abstracted into a `ValueHrefGenerator`.
Given an object type name ('User', 'Content', ...), and a set of parameters,
it is able to return the link to the value object's REST resource.
