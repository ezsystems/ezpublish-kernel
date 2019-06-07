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

        $serviceMock->expects($this->exactly(1))->method('createContentTypeGroup')->with(...$parameters);

        $decoratedService->createContentTypeGroup(...$parameters);
    }

    public function testLoadContentTypeGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce0ffda1.73499446',
            ['random_value_5ced05ce0ffde5.37998562'],
        ];

        $serviceMock->expects($this->exactly(1))->method('loadContentTypeGroup')->with(...$parameters);

        $decoratedService->loadContentTypeGroup(...$parameters);
    }

    public function testLoadContentTypeGroupByIdentifierDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce0ffe39.59526434',
            ['random_value_5ced05ce0ffe45.95635954'],
        ];

        $serviceMock->expects($this->exactly(1))->method('loadContentTypeGroupByIdentifier')->with(...$parameters);

        $decoratedService->loadContentTypeGroupByIdentifier(...$parameters);
    }

    public function testLoadContentTypeGroupsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [['random_value_5ced05ce0ffe73.64294893']];

        $serviceMock->expects($this->exactly(1))->method('loadContentTypeGroups')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('updateContentTypeGroup')->with(...$parameters);

        $decoratedService->updateContentTypeGroup(...$parameters);
    }

    public function testDeleteContentTypeGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentTypeGroup::class)];

        $serviceMock->expects($this->exactly(1))->method('deleteContentTypeGroup')->with(...$parameters);

        $decoratedService->deleteContentTypeGroup(...$parameters);
    }

    public function testCreateContentTypeDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentTypeCreateStruct::class),
            ['random_value_5ced05ce102210.02830368'],
        ];

        $serviceMock->expects($this->exactly(1))->method('createContentType')->with(...$parameters);

        $decoratedService->createContentType(...$parameters);
    }

    public function testLoadContentTypeDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce102297.95671709',
            ['random_value_5ced05ce1022b3.56541050'],
        ];

        $serviceMock->expects($this->exactly(1))->method('loadContentType')->with(...$parameters);

        $decoratedService->loadContentType(...$parameters);
    }

    public function testLoadContentTypeByIdentifierDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce1022f0.22217017',
            ['random_value_5ced05ce102306.63870627'],
        ];

        $serviceMock->expects($this->exactly(1))->method('loadContentTypeByIdentifier')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('loadContentTypeByRemoteId')->with(...$parameters);

        $decoratedService->loadContentTypeByRemoteId(...$parameters);
    }

    public function testLoadContentTypeDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce102353.24566517'];

        $serviceMock->expects($this->exactly(1))->method('loadContentTypeDraft')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('loadContentTypeList')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('loadContentTypes')->with(...$parameters);

        $decoratedService->loadContentTypes(...$parameters);
    }

    public function testCreateContentTypeDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentType::class)];

        $serviceMock->expects($this->exactly(1))->method('createContentTypeDraft')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('updateContentTypeDraft')->with(...$parameters);

        $decoratedService->updateContentTypeDraft(...$parameters);
    }

    public function testDeleteContentTypeDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentType::class)];

        $serviceMock->expects($this->exactly(1))->method('deleteContentType')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('copyContentType')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('assignContentTypeGroup')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('unassignContentTypeGroup')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('addFieldDefinition')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('removeFieldDefinition')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('updateFieldDefinition')->with(...$parameters);

        $decoratedService->updateFieldDefinition(...$parameters);
    }

    public function testPublishContentTypeDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentTypeDraft::class)];

        $serviceMock->expects($this->exactly(1))->method('publishContentTypeDraft')->with(...$parameters);

        $decoratedService->publishContentTypeDraft(...$parameters);
    }

    public function testNewContentTypeGroupCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce105ab6.78784768'];

        $serviceMock->expects($this->exactly(1))->method('newContentTypeGroupCreateStruct')->with(...$parameters);

        $decoratedService->newContentTypeGroupCreateStruct(...$parameters);
    }

    public function testNewContentTypeCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce105af0.66964747'];

        $serviceMock->expects($this->exactly(1))->method('newContentTypeCreateStruct')->with(...$parameters);

        $decoratedService->newContentTypeCreateStruct(...$parameters);
    }

    public function testNewContentTypeUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $serviceMock->expects($this->exactly(1))->method('newContentTypeUpdateStruct')->with();

        $decoratedService->newContentTypeUpdateStruct();
    }

    public function testNewContentTypeGroupUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $serviceMock->expects($this->exactly(1))->method('newContentTypeGroupUpdateStruct')->with();

        $decoratedService->newContentTypeGroupUpdateStruct();
    }

    public function testNewFieldDefinitionCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce105b49.84434846',
            'random_value_5ced05ce105b57.01268982',
        ];

        $serviceMock->expects($this->exactly(1))->method('newFieldDefinitionCreateStruct')->with(...$parameters);

        $decoratedService->newFieldDefinitionCreateStruct(...$parameters);
    }

    public function testNewFieldDefinitionUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $serviceMock->expects($this->exactly(1))->method('newFieldDefinitionUpdateStruct')->with();

        $decoratedService->newFieldDefinitionUpdateStruct();
    }

    public function testIsContentTypeUsedDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ContentType::class)];

        $serviceMock->expects($this->exactly(1))->method('isContentTypeUsed')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('removeContentTypeTranslation')->with(...$parameters);

        $decoratedService->removeContentTypeTranslation(...$parameters);
    }
}
