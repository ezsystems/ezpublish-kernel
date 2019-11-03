<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\SPI\Repository\Decorator\ContentTypeServiceDecorator;

class ContentTypeServiceDecoratorTest extends TestCase
{
    protected function createDecorator(MockObject $service): ContentTypeService
    {
        return new class($service) extends ContentTypeServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(ContentTypeService::class);
    }

    public function testCreateContentTypeGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentTypeGroupCreateStruct::class)];

        $serviceMock->expects($this->once())->method('createContentTypeGroup')->with(...$parameters);

        $decoratedService->createContentTypeGroup(...$parameters);
    }

    public function testLoadContentTypeGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            1,
            ['prioritized_language_value'],
        ];

        $serviceMock->expects($this->once())->method('loadContentTypeGroup')->with(...$parameters);

        $decoratedService->loadContentTypeGroup(...$parameters);
    }

    public function testLoadContentTypeGroupByIdentifierDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'content_group_type_identifier',
            ['prioritized_language_value'],
        ];

        $serviceMock->expects($this->once())->method('loadContentTypeGroupByIdentifier')->with(...$parameters);

        $decoratedService->loadContentTypeGroupByIdentifier(...$parameters);
    }

    public function testLoadContentTypeGroupsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [['prioritized_language_value']];

        $serviceMock->expects($this->once())->method('loadContentTypeGroups')->with(...$parameters);

        $decoratedService->loadContentTypeGroups(...$parameters);
    }

    public function testUpdateContentTypeGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentTypeGroup::class),
            $this->createMock(ContentTypeGroupUpdateStruct::class),
        ];

        $serviceMock->expects($this->once())->method('updateContentTypeGroup')->with(...$parameters);

        $decoratedService->updateContentTypeGroup(...$parameters);
    }

    public function testDeleteContentTypeGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentTypeGroup::class)];

        $serviceMock->expects($this->once())->method('deleteContentTypeGroup')->with(...$parameters);

        $decoratedService->deleteContentTypeGroup(...$parameters);
    }

    public function testCreateContentTypeDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentTypeCreateStruct::class),
            ['content_type_group_identifier'],
        ];

        $serviceMock->expects($this->once())->method('createContentType')->with(...$parameters);

        $decoratedService->createContentType(...$parameters);
    }

    public function testLoadContentTypeDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            1,
            ['prioritized_language_value'],
        ];

        $serviceMock->expects($this->once())->method('loadContentType')->with(...$parameters);

        $decoratedService->loadContentType(...$parameters);
    }

    public function testLoadContentTypeByIdentifierDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'content_type_identifier',
            ['prioritized_language_value'],
        ];

        $serviceMock->expects($this->once())->method('loadContentTypeByIdentifier')->with(...$parameters);

        $decoratedService->loadContentTypeByIdentifier(...$parameters);
    }

    public function testLoadContentTypeByRemoteIdDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce102320.80314468',
            ['random_value_5ced05ce102338.43562070'],
        ];

        $serviceMock->expects($this->once())->method('loadContentTypeByRemoteId')->with(...$parameters);

        $decoratedService->loadContentTypeByRemoteId(...$parameters);
    }

    public function testLoadContentTypeDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [1, true];

        $serviceMock->expects($this->once())->method('loadContentTypeDraft')->with(...$parameters);

        $decoratedService->loadContentTypeDraft(...$parameters);
    }

    public function testLoadContentTypeListDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            ['random_value_5ced05ce102385.63342303'],
            ['random_value_5ced05ce102394.49260758'],
        ];

        $serviceMock->expects($this->once())->method('loadContentTypeList')->with(...$parameters);

        $decoratedService->loadContentTypeList(...$parameters);
    }

    public function testLoadContentTypesDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentTypeGroup::class),
            ['random_value_5ced05ce1023d7.35531928'],
        ];

        $serviceMock->expects($this->once())->method('loadContentTypes')->with(...$parameters);

        $decoratedService->loadContentTypes(...$parameters);
    }

    public function testCreateContentTypeDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentType::class)];

        $serviceMock->expects($this->once())->method('createContentTypeDraft')->with(...$parameters);

        $decoratedService->createContentTypeDraft(...$parameters);
    }

    public function testUpdateContentTypeDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(ContentTypeUpdateStruct::class),
        ];

        $serviceMock->expects($this->once())->method('updateContentTypeDraft')->with(...$parameters);

        $decoratedService->updateContentTypeDraft(...$parameters);
    }

    public function testDeleteContentTypeDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentType::class)];

        $serviceMock->expects($this->once())->method('deleteContentType')->with(...$parameters);

        $decoratedService->deleteContentType(...$parameters);
    }

    public function testCopyContentTypeDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(User::class),
        ];

        $serviceMock->expects($this->once())->method('copyContentType')->with(...$parameters);

        $decoratedService->copyContentType(...$parameters);
    }

    public function testAssignContentTypeGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(ContentTypeGroup::class),
        ];

        $serviceMock->expects($this->once())->method('assignContentTypeGroup')->with(...$parameters);

        $decoratedService->assignContentTypeGroup(...$parameters);
    }

    public function testUnassignContentTypeGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(ContentTypeGroup::class),
        ];

        $serviceMock->expects($this->once())->method('unassignContentTypeGroup')->with(...$parameters);

        $decoratedService->unassignContentTypeGroup(...$parameters);
    }

    public function testAddFieldDefinitionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinitionCreateStruct::class),
        ];

        $serviceMock->expects($this->once())->method('addFieldDefinition')->with(...$parameters);

        $decoratedService->addFieldDefinition(...$parameters);
    }

    public function testRemoveFieldDefinitionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinition::class),
        ];

        $serviceMock->expects($this->once())->method('removeFieldDefinition')->with(...$parameters);

        $decoratedService->removeFieldDefinition(...$parameters);
    }

    public function testUpdateFieldDefinitionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinition::class),
            $this->createMock(FieldDefinitionUpdateStruct::class),
        ];

        $serviceMock->expects($this->once())->method('updateFieldDefinition')->with(...$parameters);

        $decoratedService->updateFieldDefinition(...$parameters);
    }

    public function testPublishContentTypeDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentTypeDraft::class)];

        $serviceMock->expects($this->once())->method('publishContentTypeDraft')->with(...$parameters);

        $decoratedService->publishContentTypeDraft(...$parameters);
    }

    public function testNewContentTypeGroupCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce105ab6.78784768'];

        $serviceMock->expects($this->once())->method('newContentTypeGroupCreateStruct')->with(...$parameters);

        $decoratedService->newContentTypeGroupCreateStruct(...$parameters);
    }

    public function testNewContentTypeCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce105af0.66964747'];

        $serviceMock->expects($this->once())->method('newContentTypeCreateStruct')->with(...$parameters);

        $decoratedService->newContentTypeCreateStruct(...$parameters);
    }

    public function testNewContentTypeUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('newContentTypeUpdateStruct')->with(...$parameters);

        $decoratedService->newContentTypeUpdateStruct(...$parameters);
    }

    public function testNewContentTypeGroupUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('newContentTypeGroupUpdateStruct')->with(...$parameters);

        $decoratedService->newContentTypeGroupUpdateStruct(...$parameters);
    }

    public function testNewFieldDefinitionCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce105b49.84434846',
            'random_value_5ced05ce105b57.01268982',
        ];

        $serviceMock->expects($this->once())->method('newFieldDefinitionCreateStruct')->with(...$parameters);

        $decoratedService->newFieldDefinitionCreateStruct(...$parameters);
    }

    public function testNewFieldDefinitionUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('newFieldDefinitionUpdateStruct')->with(...$parameters);

        $decoratedService->newFieldDefinitionUpdateStruct(...$parameters);
    }

    public function testIsContentTypeUsedDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentType::class)];

        $serviceMock->expects($this->once())->method('isContentTypeUsed')->with(...$parameters);

        $decoratedService->isContentTypeUsed(...$parameters);
    }

    public function testRemoveContentTypeTranslationDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            'random_value_5ced05ce105c21.42399370',
        ];

        $serviceMock->expects($this->once())->method('removeContentTypeTranslation')->with(...$parameters);

        $decoratedService->removeContentTypeTranslation(...$parameters);
    }
}
