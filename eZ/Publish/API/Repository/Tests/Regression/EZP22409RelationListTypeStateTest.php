<?php

/**
 * File containing the EZP22409RelationListTypeStateTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\Core\FieldType\RelationList;
use DateTime;

/**
 * Test case for RelationList using alterate ContentType states issue in EZP-22409.
 *
 * Issue EZP-22409
 */
class EZP22409RelationListTypeStateTest extends BaseTest
{
    protected function setUp()
    {
        parent::setUp();

        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();

        $creatorId = $repository->getCurrentUser()->id;
        $creationDate = new DateTime();

        // create ContentType
        $typeCreateStruct = $contentTypeService->newContentTypeCreateStruct(
            'test-type'
        );
        $typeCreateStruct->names = [
            'eng-GB' => 'title',
        ];
        $typeCreateStruct->descriptions = [
            'eng-GB' => 'description',
        ];
        $typeCreateStruct->remoteId = 'new-remoteid';
        $typeCreateStruct->creatorId = $creatorId;
        $typeCreateStruct->creationDate = $creationDate;
        $typeCreateStruct->mainLanguageCode = 'eng-GB';
        $typeCreateStruct->nameSchema = '<title>';
        $typeCreateStruct->urlAliasSchema = '<title>';

        // create content fields
        $titleFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title',
            'ezstring'
        );
        $titleFieldCreate->names = [
            'eng-GB' => 'title',
        ];
        $titleFieldCreate->descriptions = [
            'eng-GB' => 'title description',
        ];
        $titleFieldCreate->fieldGroup = 'content';
        $titleFieldCreate->position = 1;
        $titleFieldCreate->isTranslatable = true;
        $titleFieldCreate->isRequired = true;
        $titleFieldCreate->isInfoCollector = false;
        $titleFieldCreate->isSearchable = true;
        $titleFieldCreate->defaultValue = 'New text line';
        $typeCreateStruct->addFieldDefinition($titleFieldCreate);

        $objectRelationListFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'relationlist',
            'ezobjectrelationlist'
        );
        $objectRelationListFieldCreate->names = [
            'eng-GB' => 'object relation list',
        ];
        $objectRelationListFieldCreate->descriptions = [
            'eng-GB' => 'object relation list description',
        ];
        $objectRelationListFieldCreate->fieldGroup = 'content';
        $objectRelationListFieldCreate->position = 2;
        $objectRelationListFieldCreate->isTranslatable = false;
        $objectRelationListFieldCreate->isRequired = false;
        $objectRelationListFieldCreate->isInfoCollector = false;
        $objectRelationListFieldCreate->isSearchable = false;
        $objectRelationListFieldCreate->defaultValue = '';
        $typeCreateStruct->addFieldDefinition($objectRelationListFieldCreate);

        // ContentType Group
        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'test-group'
        );
        $groupCreate->creatorId = $creatorId;
        $groupCreate->creationDate = $creationDate;

        // create and publish ContentType
        $type = $contentTypeService->createContentType(
            $typeCreateStruct,
            [$contentTypeService->createContentTypeGroup($groupCreate)]
        );
        $contentTypeService->publishContentTypeDraft($type);
    }

    public function testCreateObjectWithRelationToContentType()
    {
        $this->createContentWithRelationList();
    }

    public function testCreateObjectWithRelationToContentTypeWithExistingDraft()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $contentTypeDraft = $contentTypeService->createContentTypeDraft(
            $contentTypeService->loadContentTypeByIdentifier('folder')
        );

        $this->createContentWithRelationList();
    }

    /**
     * Creates content #2 of type 'test-type' with a relation list to new content #1 of type 'folder'.
     */
    private function createContentWithRelationList()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $contentTypeService = $repository->getContentTypeService();

        // create destination content
        $contentCreateStruct1 = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('folder'),
            'eng-GB'
        );
        $contentCreateStruct1->setField('name', 'EZP-22409-2');
        $draft1 = $contentService->createContent(
            $contentCreateStruct1,
            [$locationService->newLocationCreateStruct(2)]
        );
        $destinationContent = $contentService->publishVersion($draft1->versionInfo);

        // create source content #1
        $contentCreateStruct2 = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('test-type'),
            'eng-GB'
        );
        $contentCreateStruct2->setField('title', 'EZP-22409-1');
        $contentCreateStruct2->setField(
            'relationlist',
            new RelationList\Value([$destinationContent->id])
        );
        $draft2 = $contentService->createContent(
            $contentCreateStruct2,
            [$locationService->newLocationCreateStruct(2)]
        );
        $content2 = $contentService->publishVersion($draft2->versionInfo);
    }
}
