# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* Handling of multilingual Content Types was improved in the following Public API methods:
    - `\eZ\Publish\API\Repository\ContentTypeService::updateContentTypeDraft`,
    - `\eZ\Publish\API\Repository\ContentTypeService::addFieldDefinition`,
    - `\eZ\Publish\API\Repository\ContentTypeService::updateFieldDefinition`. 
    
    Passing translations in all languages is no longer needed, all you have to do is pass language version 
    you wish to modify.
* New method `\eZ\Publish\API\Repository\ContentTypeService::removeContentTypeTranslation` is introduced.

## Deprecations

* `eZ\Publish\Core\FieldType\RichText` namespace has been deprecated and will be removed in 8.0.

  Enable [eZ Platform RichText Bundle](https://github.com/ezsystems/ezplatform-richtext) instead.
