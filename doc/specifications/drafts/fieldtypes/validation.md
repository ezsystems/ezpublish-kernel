### FieldTypes/Repository validation

Goals:
- make field definition validation exportable
- Replace FieldType validation with Symfony's
- Make it easy to implement as a 3rd party
- Make validation applicable by repository-forms

### Validate repository values
The idea of Symfony validation is that you validate a complete value.

A `ContentCreateStruct` would be validated as a whole, and per field:
- mainLanguageCode must be set
- contentType must be set
- if remoteId is set, it should not exist in the system
- required fields must have a value
- fields values should pass their field definition's constraints

However, most operations expect an updateStruct AND a value object:

```php
public function createContent(APIContentCreateStruct $contentCreateStruct, array $locationCreateStructs = array())
public function updateContent(APIVersionInfo $versionInfo, APIContentUpdateStruct $contentUpdateStruct);
public function updateContentMetadata(ContentInfo $contentInfo, ContentMetadataUpdateStruct $contentMetadataUpdateStruct);
```

repository-forms makes up for that by using aggregate objects: a `ContentCreateData`
aggregates LocationCreateStructs and FieldData objects. Should a `ContentCreate` object
be introduced, that contains _everything_ needed to create a content, be introduced ?

#### ContentCreate, ContentUpdate objects ?
They could be validated directly, without even going through the ContentService.
Is it something we want or ?
 
However, remember that `ContentCreate`/`ContentUpdate` are shaped by the `ContentType`.

Does this bring us to more Domain shaped objects ?

A content form is rendered based on the `Content(Create|Update)` that is _built_,
based on a given `ContentType`, for this purpose. Do we need an item on a higher level than
the `ContentService` ? At the moment, this is done by the `Content(Create|Update)Mapper`.

```php
$data = (new ContentCreateMapper())->mapToFormData(
    $contentType, [
        'mainLanguageCode' => $language,
        'parentLocation' => $this->locationService->newLocationCreateStruct($parentLocationId),
    ]
);
```

Where is this done in repository-forms ? It starts from the controller action,
since it has the content type id, language and location parameters.

> Code notes from ContentService::
>  Use validator on the $contentCreateStruct itself => validates an objectr
>  We should be able to use the same validation on the object that gets built by repository forms
>  Is there any way to ensure that we don't validate twice what comes from forms ?
>  Or can we rely on the repository's validation alone, since it uses the validation component ?
>  Plan A: Validate here AND repository-forms, read constraints from the FieldTypes in repo-forms
>  Plan B: Let data pass the form, and run repository validation only ?

#### FieldType validation
Right now, Field value validation is the FieldType's responsibility, using `validate()`. Each FieldType has its own
implementation of `validate()`, that returns an array of `SPI\FieldType\ValidationError`.

However, repository-forms will run this FieldType validation without the need for any extra code,
through the `FieldValueValidator`. Changing the Core to use Symfony validators doesn't have much value
besides "doing the right thing".

Could we explore annotations or validator configuration on FieldType Values ? TextLine, Integer...
Is it what we want ? It might be what some 3rd party want.

```php
$validator->validate($textLineValue);
```

This call can't work as is, since a TextLine Value has to be validated _against a FieldDefinition_.
This is where the type is, and this is where the type's options are configured.

It gets obvious with this configuration:

```yaml
eZ\Publish\Core\FieldType\TextLine\Value:
    properties:
        text:
            - Length:
                max: settings.maxLength
                min: settings.minLength
```

How is `settings` interpreted ? When ?

> Do we need standardized descriptions of FieldType Values properties, so that we can "map"
> the properties to validation, forms... ?

What about annotations ?
They could make sense here, even though we may have to skew the syntax a bit,
again because of preferences.

```php
// FieldType/TextLine/Value.php
/**
 * @Assert\Length(
 *      min = definition.minLength,
 *      max = definition.maxLength,
 *      minMessage = "...",
 *      maxMessage = "..."
 * )
 */
public $text;

// FieldType/TextLine/Definition.php
/**
 * @Assert\Type("int")
 * @Assert\Range(min = 0, max = 255)
 */
public $minLength;
```

*Note: if each FieldType had a Definition value object in addition to Type and Value,
we could make it easier to implement editing & validating a field definition.*

> Does it limit extensibility of FieldTypes (custom validation of an existing FieldType).
>   What about custom Values that inherit from the base one and customize validation ?
>   `MyRegexTextLineValue`
>   `MyZipCodeTextLineValue`

The main question is **how complex is the code required to implement a FieldType edit form**.

#### Generated FormType objects per FieldType/ContentType
In a completely different direction, doctrine uses a console command to generate a FormType from
a Doctrine Entity. Could we consider generating a FormType based on a FieldType ? On a ContentType ?

```php
php app/console ez:generate:content-type-form type_identifier
```

Is it gonna work for developers given that ContentTypes are defined using the UI ?
### Random code


```php
$validator->validate($contentCreateStruct);
```

```php`
// This wont' work as we need the Content(Info) that is updated
$validator->validate($contentUpdateStruct);

// This would work
$contentUpdate = new ContentUpdate($content, $contentUpdateStruct);
$validator->validate($contentUpdate);
``
