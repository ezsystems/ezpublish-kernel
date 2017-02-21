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

How can we move that validation towards the Symfony component, and make it usable by the form component ?
Do we really need to do this ? It is precisely what repository-forms does, but the side effect is that it complexifies
development by requiring more FieldType code.

To integrate a FieldType into 
- mapping the FieldType\Value to a Form object, using the FieldDefinition settings
- ~~mapping the FieldDefinition validation & settings to form validators ?~~
  validation is covered using a global `FieldValueValidator`, that executes the FieldTypes `validate()`
  method of the fieldtype being validated

Can we go as far as using annotations on a FieldValue ?
Is it what we want ? It might be what some 3rd party want.
Does it limit extensibility of FieldTypes (custom validation of an existing FieldType).
  What about custom Values that inherit from the base one and customize validation ?
  `MyRegexTextLineValue`
  `MyZipCodeTextLineValue`

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
