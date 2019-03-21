<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\ContentTypeService as APIService;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\Core\Repository\SiteAccessAware\ContentTypeService;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Repository\Values\User\User;

class ContentTypeServiceTest extends AbstractServiceTest
{
    public function getAPIServiceClassName()
    {
        return APIService::class;
    }

    public function getSiteAccessAwareServiceClassName()
    {
        return ContentTypeService::class;
    }

    public function providerForPassTroughMethods()
    {
        $contentTypeGroupCreateStruct = new ContentTypeGroupCreateStruct();
        $contentTypeGroupUpdateStruct = new ContentTypeGroupUpdateStruct();
        $contentTypeGroup = new ContentTypeGroup();

        $contentTypeCreateStruct = new ContentTypeCreateStruct();
        $contentTypeUpdateStruct = new ContentTypeUpdateStruct();
        $contentType = new ContentType();
        $contentTypeDraft = new ContentTypeDraft();

        $fieldDefinition = new FieldDefinition();
        $fieldDefinitionCreateStruct = new FieldDefinitionCreateStruct();
        $fieldDefinitionUpdateStruct = new FieldDefinitionUpdateStruct();

        $user = new User();

        // string $method, array $arguments, bool $return = true
        return [
            ['createContentTypeGroup', [$contentTypeGroupCreateStruct]],

            ['updateContentTypeGroup', [$contentTypeGroup, $contentTypeGroupUpdateStruct]],

            ['deleteContentTypeGroup', [$contentTypeGroup]],

            ['createContentType', [$contentTypeCreateStruct, [$contentTypeGroup]]],

            ['loadContentTypeDraft', [22]],

            ['createContentTypeDraft', [$contentType]],

            ['updateContentTypeDraft', [$contentTypeDraft, $contentTypeUpdateStruct]],

            ['deleteContentType', [$contentType]],

            ['copyContentType', [$contentType]],
            ['copyContentType', [$contentType, $user]],

            ['assignContentTypeGroup', [$contentType, $contentTypeGroup]],

            ['unassignContentTypeGroup', [$contentType, $contentTypeGroup]],

            ['addFieldDefinition', [$contentTypeDraft, $fieldDefinitionCreateStruct]],

            ['removeFieldDefinition', [$contentTypeDraft, $fieldDefinition]],

            ['updateFieldDefinition', [$contentTypeDraft, $fieldDefinition, $fieldDefinitionUpdateStruct]],

            ['publishContentTypeDraft', [$contentTypeDraft]],

            ['newContentTypeGroupCreateStruct', ['media']],

            ['newContentTypeCreateStruct', ['blog']],

            ['newContentTypeUpdateStruct', []],

            ['newContentTypeGroupUpdateStruct', []],

            ['newFieldDefinitionCreateStruct', ['body', 'ezrichtext']],

            ['newFieldDefinitionUpdateStruct', []],

            ['isContentTypeUsed', [$contentType]],

            ['removeContentTypeTranslation', [$contentTypeDraft, 'ger-DE'], $contentTypeDraft],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        $contentTypeGroup = new ContentTypeGroup();

        // string $method, array $arguments, bool $return, int $languageArgumentIndex
        return [
            ['loadContentTypeGroup', [33, self::LANG_ARG], true, 1],

            ['loadContentTypeGroupByIdentifier', ['content', self::LANG_ARG], true, 1],

            ['loadContentTypeGroups', [self::LANG_ARG], true, 0],

            ['loadContentType', [22, self::LANG_ARG], true, 1],

            ['loadContentTypeByIdentifier', ['article', self::LANG_ARG], true, 1],

            ['loadContentTypeByRemoteId', ['w4ini3tn4f', self::LANG_ARG], true, 1],

            ['loadContentTypes', [$contentTypeGroup, self::LANG_ARG], true, 1],
        ];
    }
}
