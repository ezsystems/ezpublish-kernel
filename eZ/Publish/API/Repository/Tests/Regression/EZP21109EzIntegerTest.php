<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Persistence\Legacy\Exception\TypeNotFound as TypeNotFoundException;

/**
 * Regression tests for the issue EZP-21109.
 */
class EZP21109EzIntegerTest extends BaseTest
{
    /**
     * The short name of the current class.
     *
     * @var string
     */
    protected $classShortName;

    /** @var ContentType */
    protected $contentType;

    protected function setUp()
    {
        parent::setUp();

        $reflect = new \ReflectionClass($this);
        $this->classShortName = $reflect->getShortName();

        $this->contentType = $this->createTestContentType();
    }

    protected function tearDown()
    {
        $this->deleteTestContentType();
        parent::tearDown();
    }

    /**
     * Assert that it is possible to store any integer value in an integer field with default settings.
     *
     * @dataProvider validIntegerValues
     */
    public function testEzIntegerWithDefaultValues($integerValue)
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $contentCreateStruct = $contentService->newContentCreateStruct($this->contentType, 'eng-GB');
        $contentCreateStruct->setField('test', $integerValue);

        $location = $locationService->newLocationCreateStruct(2);

        $draft = $contentService->createContent($contentCreateStruct, [$location]);

        $contentService->publishVersion($draft->versionInfo);

        $content = $contentService->loadContent($draft->versionInfo->contentInfo->id);

        /** @var \eZ\Publish\Core\FieldType\Integer\Value $fieldValue */
        $fieldValue = $content->getFieldValue('test');

        $this->assertInstanceOf('eZ\Publish\Core\FieldType\Integer\Value', $fieldValue);

        $this->assertEquals($integerValue, $fieldValue->value);

        $contentService->deleteContent($content->versionInfo->contentInfo);
    }

    public function validIntegerValues()
    {
        return [
            [0],
            [1],
            [-1],
            [2147483647],
            [-2147483647],
        ];
    }

    /**
     * Creates a Test ContentType for this test holding an ezintegerfield.
     *
     * @return ContentType
     */
    protected function createTestContentType()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        // Create a test class with an integer field type
        $typeGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');

        $contentType = $contentTypeService->newContentTypeCreateStruct($this->classShortName);
        $contentType->creatorId = $repository->getCurrentUser()->id;
        $contentType->mainLanguageCode = 'eng-GB';
        $contentType->names = [
            'eng-GB' => $this->classShortName,
        ];
        $contentType->nameSchema = '<test>';
        $contentType->urlAliasSchema = '<test>';
        $contentType->isContainer = false;
        $contentType->defaultAlwaysAvailable = true;

        // Field: IntegerTest
        $field = $contentTypeService->newFieldDefinitionCreateStruct('test', 'ezinteger');
        $field->names = [
            'eng-GB' => 'Test',
        ];
        $field->position = 10;
        $contentType->addFieldDefinition($field);

        $draft = $contentTypeService->createContentType($contentType, [$typeGroup]);

        $contentTypeService->publishContentTypeDraft($draft);

        return $contentTypeService->loadContentTypeByIdentifier($this->classShortName);
    }

    /**
     * Deletes the Test ContentType for this test.
     */
    protected function deleteTestContentType()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        try {
            $contentType = $contentTypeService->loadContentTypeByIdentifier($this->classShortName);
            $contentTypeService->deleteContentType($contentType);
        } catch (TypeNotFoundException $e) {
            // This shouldn't throw an error
        }
    }
}
