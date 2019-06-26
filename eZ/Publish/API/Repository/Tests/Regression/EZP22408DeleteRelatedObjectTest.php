<?php

/**
 * @copyright: Copyright (C) 2014 Heliopsis. All rights reserved.
 * @license: proprietary
 */
namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\Core\FieldType\RelationList\Value as RelationListValue;
use eZ\Publish\Core\FieldType\Relation\Value as RelationValue;

class EZP22408DeleteRelatedObjectTest extends BaseTest
{
    /** @var ContentType */
    private $testContentType;

    protected function setUp()
    {
        parent::setUp();

        $this->createTestContentType();
    }

    public function testRelationListIsUpdatedWhenRelatedObjectIsDeleted()
    {
        $targetObject1 = $this->createTargetObject('Relation list target object 1');
        $targetObject2 = $this->createTargetObject('Relation list target object 2');
        $referenceObject = $this->createReferenceObject(
            'Reference object',
            [
                $targetObject1->id,
                $targetObject2->id,
            ]
        );

        $contentService = $this->getRepository()->getContentService();
        $contentService->deleteContent($targetObject1->contentInfo);

        $reloadedReferenceObject = $contentService->loadContent($referenceObject->id);
        /** @var RelationListValue */
        $relationListValue = $reloadedReferenceObject->getFieldValue('relation_list');
        $this->assertSame([$targetObject2->id], $relationListValue->destinationContentIds);
    }

    public function testSingleRelationIsUpdatedWhenRelatedObjectIsDeleted()
    {
        $targetObject = $this->createTargetObject('Single relation target object');
        $referenceObject = $this->createReferenceObject(
            'Reference object',
            [],
            $targetObject->id
        );

        $contentService = $this->getRepository()->getContentService();
        $contentService->deleteContent($targetObject->contentInfo);

        $reloadedReferenceObject = $contentService->loadContent($referenceObject->id);
        /** @var RelationValue */
        $relationValue = $reloadedReferenceObject->getFieldValue('single_relation');
        $this->assertEmpty($relationValue->destinationContentId);
    }

    private function createTestContentType()
    {
        $languageCode = $this->getMainLanguageCode();
        $contentTypeService = $this->getRepository()->getContentTypeService();

        $createStruct = $contentTypeService->newContentTypeCreateStruct('test_content_type');
        $createStruct->mainLanguageCode = $languageCode;
        $createStruct->names = [$languageCode => 'Test Content Type'];
        $createStruct->nameSchema = '<name>';
        $createStruct->urlAliasSchema = '<name>';

        $createStruct->addFieldDefinition(
            new FieldDefinitionCreateStruct(
                [
                    'fieldTypeIdentifier' => 'ezstring',
                    'identifier' => 'name',
                    'names' => [$languageCode => 'Name'],
                    'position' => 1,
                ]
            )
        );

        $createStruct->addFieldDefinition(
            new FieldDefinitionCreateStruct(
                [
                    'fieldTypeIdentifier' => 'ezobjectrelationlist',
                    'identifier' => 'relation_list',
                    'names' => [$languageCode => 'Relation List'],
                    'position' => 2,
                ]
            )
        );

        $createStruct->addFieldDefinition(
            new FieldDefinitionCreateStruct(
                [
                    'fieldTypeIdentifier' => 'ezobjectrelation',
                    'identifier' => 'single_relation',
                    'names' => [$languageCode => 'Single Relation'],
                    'position' => 3,
                ]
            )
        );

        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');
        $this->testContentType = $contentTypeService->createContentType($createStruct, [$contentGroup]);
        $contentTypeService->publishContentTypeDraft($this->testContentType);
    }

    private function getMainLanguageCode()
    {
        return $this->getRepository()->getContentLanguageService()->getDefaultLanguageCode();
    }

    /**
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    private function createTargetObject($name)
    {
        $contentService = $this->getRepository()->getContentService();
        $createStruct = $contentService->newContentCreateStruct(
            $this->testContentType,
            $this->getMainLanguageCode()
        );
        $createStruct->setField('name', $name);

        $object = $contentService->createContent(
            $createStruct,
            [
                $this->getLocationCreateStruct(),
            ]
        );

        return $contentService->publishVersion($object->versionInfo);
    }

    /**
     * @param string $name
     * @param array $relationListTarget Array of destination content ids
     * @param id $singleRelationTarget Content id
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    private function createReferenceObject($name, array $relationListTarget = [], $singleRelationTarget = null)
    {
        $contentService = $this->getRepository()->getContentService();
        $createStruct = $contentService->newContentCreateStruct(
            $this->testContentType,
            $this->getMainLanguageCode()
        );

        $createStruct->setField('name', $name);
        if (!empty($relationListTarget)) {
            $createStruct->setField('relation_list', $relationListTarget);
        }

        if ($singleRelationTarget) {
            $createStruct->setField('single_relation', $singleRelationTarget);
        }

        $object = $contentService->createContent(
            $createStruct,
            [
                $this->getLocationCreateStruct(),
            ]
        );

        return $contentService->publishVersion($object->versionInfo);
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct
     */
    private function getLocationCreateStruct()
    {
        return $this->getRepository()->getLocationService()->newLocationCreateStruct(2);
    }
}
