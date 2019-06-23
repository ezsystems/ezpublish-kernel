<?php

/**
 * File containing the NonRedundantFieldSetTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;

/**
 * Base class for for create and update Content operations in the ContentService with regard to
 * non-redundant set of fields being passed to the storage.
 */
abstract class BaseNonRedundantFieldSetTest extends BaseTest
{
    /**
     * Creates a fully functional ContentType and returns it.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createContentType()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $creatorId = $this->generateId('user', 14);

        $typeCreate = $contentTypeService->newContentTypeCreateStruct('test');
        $typeCreate->mainLanguageCode = 'eng-US';
        $typeCreate->remoteId = '384b94a1bd6bc06826410e284dd9684887bf56fc';
        $typeCreate->urlAliasSchema = '<field1|field2|field3|field4>';
        $typeCreate->nameSchema = '<field1|field2|field3|field4>';
        $typeCreate->names = ['eng-US' => 'Blog post'];
        $typeCreate->descriptions = ['eng-US' => 'A blog post'];
        $typeCreate->creatorId = $creatorId;
        $typeCreate->creationDate = $this->createDateTime();

        $validatorConfiguration = [
            'StringLengthValidator' => [
                'minStringLength' => null,
                'maxStringLength' => null,
            ],
        ];

        // Field #1
        $field1Create = $contentTypeService->newFieldDefinitionCreateStruct('field1', 'ezstring');
        $field1Create->names = ['eng-US' => 'Field #1'];
        $field1Create->descriptions = ['eng-US' => 'Field #1 is not translatable and has empty default value'];
        $field1Create->fieldGroup = 'test';
        $field1Create->position = 1;
        $field1Create->isTranslatable = false;
        $field1Create->isRequired = false;
        $field1Create->isInfoCollector = false;
        $field1Create->validatorConfiguration = $validatorConfiguration;
        $field1Create->fieldSettings = [];
        $field1Create->isSearchable = true;
        $field1Create->defaultValue = null;

        $typeCreate->addFieldDefinition($field1Create);

        // Field #2
        $field2Create = $contentTypeService->newFieldDefinitionCreateStruct('field2', 'ezstring');
        $field2Create->names = ['eng-US' => 'Field #2'];
        $field2Create->descriptions = ['eng-US' => 'Field #2 is not translatable and has non-empty default value'];
        $field2Create->fieldGroup = 'test';
        $field2Create->position = 2;
        $field2Create->isTranslatable = false;
        $field2Create->isRequired = false;
        $field2Create->isInfoCollector = false;
        $field2Create->validatorConfiguration = $validatorConfiguration;
        $field2Create->fieldSettings = [];
        $field2Create->isSearchable = true;
        $field2Create->defaultValue = new TextLineValue('default value 2');

        $typeCreate->addFieldDefinition($field2Create);

        // Field #3
        $field3Create = $contentTypeService->newFieldDefinitionCreateStruct('field3', 'ezstring');
        $field3Create->names = ['eng-US' => 'Field #3'];
        $field3Create->descriptions = ['eng-US' => 'Field #3 is translatable and has empty default value'];
        $field3Create->fieldGroup = 'test';
        $field3Create->position = 3;
        $field3Create->isTranslatable = true;
        $field3Create->isRequired = false;
        $field3Create->isInfoCollector = false;
        $field3Create->validatorConfiguration = $validatorConfiguration;
        $field3Create->fieldSettings = [];
        $field3Create->isSearchable = true;
        $field3Create->defaultValue = null;

        $typeCreate->addFieldDefinition($field3Create);

        // Field #4
        $field4Create = $contentTypeService->newFieldDefinitionCreateStruct('field4', 'ezstring');
        $field4Create->names = ['eng-US' => 'Field #4'];
        $field4Create->descriptions = ['eng-US' => 'Field #4 is translatable and has non empty default value'];
        $field4Create->fieldGroup = 'test';
        $field4Create->position = 4;
        $field4Create->isTranslatable = true;
        $field4Create->isRequired = false;
        $field4Create->isInfoCollector = false;
        $field4Create->validatorConfiguration = $validatorConfiguration;
        $field4Create->fieldSettings = [];
        $field4Create->isSearchable = true;
        $field4Create->defaultValue = new TextLineValue('default value 4');

        $typeCreate->addFieldDefinition($field4Create);

        $groups = [$contentTypeService->loadContentTypeGroupByIdentifier('Content')];

        $contentTypeDraft = $contentTypeService->createContentType($typeCreate, $groups);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentType($contentTypeDraft->id);

        return $contentType;
    }

    /**
     * @param string $mainLanguageCode
     * @param array $fieldValues
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createTestContent($mainLanguageCode, array $fieldValues)
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $contentType = $this->createContentType();
        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, $mainLanguageCode);
        foreach ($fieldValues as $identifier => $languageValues) {
            foreach ($languageValues as $languageCode => $value) {
                $contentCreateStruct->setField($identifier, $value, $languageCode);
            }
        }
        $contentCreateStruct->remoteId = 'abc0123456789def0123456789';
        $contentCreateStruct->alwaysAvailable = true;

        $content = $contentService->createContent($contentCreateStruct);
        $content = $contentService->loadContent($content->id, null, $content->versionInfo->versionNo);

        return $content;
    }

    protected function createMultilingualTestContent()
    {
        $fieldValues = [
            'field1' => ['eng-US' => 'value 1'],
            'field2' => ['eng-US' => 'value 2'],
            'field3' => [
                'eng-US' => 'value 3',
                'eng-GB' => 'value 3 eng-GB',
            ],
            'field4' => [
                'eng-US' => 'value 4',
                'eng-GB' => 'value 4 eng-GB',
            ],
        ];

        return $this->createTestContent('eng-US', $fieldValues);
    }

    protected function createTestContentForUpdate()
    {
        $fieldValues = [
            'field1' => ['eng-US' => 'value 1'],
            'field2' => ['eng-US' => 'value 2'],
            'field3' => [
                'eng-US' => 'value 3',
                'eng-GB' => 'value 3 eng-GB',
            ],
            'field4' => [
                'eng-US' => 'value 4',
                'eng-GB' => 'value 4 eng-GB',
            ],
        ];

        return $this->createTestContent('eng-US', $fieldValues);
    }

    protected function updateTestContent($initialLanguageCode, array $fieldValues)
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $content = $this->createTestContentForUpdate();

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = $initialLanguageCode;
        foreach ($fieldValues as $identifier => $languageValues) {
            foreach ($languageValues as $languageCode => $value) {
                $contentUpdateStruct->setField($identifier, $value, $languageCode);
            }
        }

        $content = $contentService->updateContent($content->getVersionInfo(), $contentUpdateStruct);

        return $contentService->loadContent($content->id, null, $content->versionInfo->versionNo);
    }
}
