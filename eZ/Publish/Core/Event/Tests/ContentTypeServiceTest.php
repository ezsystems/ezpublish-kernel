<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Events\Tests;

use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\Events\ContentType\AddFieldDefinitionEvent as AddFieldDefinitionEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\AssignContentTypeGroupEvent as AssignContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeAddFieldDefinitionEvent as BeforeAddFieldDefinitionEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeAssignContentTypeGroupEvent as BeforeAssignContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeCopyContentTypeEvent as BeforeCopyContentTypeEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeCreateContentTypeDraftEvent as BeforeCreateContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeCreateContentTypeEvent as BeforeCreateContentTypeEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeCreateContentTypeGroupEvent as BeforeCreateContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeDeleteContentTypeEvent as BeforeDeleteContentTypeEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeDeleteContentTypeGroupEvent as BeforeDeleteContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforePublishContentTypeDraftEvent as BeforePublishContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeRemoveContentTypeTranslationEvent as BeforeRemoveContentTypeTranslationEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeRemoveFieldDefinitionEvent as BeforeRemoveFieldDefinitionEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeUnassignContentTypeGroupEvent as BeforeUnassignContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeUpdateContentTypeDraftEvent as BeforeUpdateContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeUpdateContentTypeGroupEvent as BeforeUpdateContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeUpdateFieldDefinitionEvent as BeforeUpdateFieldDefinitionEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\CopyContentTypeEvent as CopyContentTypeEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\CreateContentTypeDraftEvent as CreateContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\CreateContentTypeEvent as CreateContentTypeEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\CreateContentTypeGroupEvent as CreateContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\DeleteContentTypeEvent as DeleteContentTypeEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\DeleteContentTypeGroupEvent as DeleteContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\PublishContentTypeDraftEvent as PublishContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\RemoveContentTypeTranslationEvent as RemoveContentTypeTranslationEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\RemoveFieldDefinitionEvent as RemoveFieldDefinitionEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\UnassignContentTypeGroupEvent as UnassignContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\UpdateContentTypeDraftEvent as UpdateContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\UpdateContentTypeGroupEvent as UpdateContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\UpdateFieldDefinitionEvent as UpdateFieldDefinitionEventInterface;
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
use eZ\Publish\API\Repository\Events\ContentTypeService;

class ContentTypeServiceTest extends AbstractServiceTest
{
    public function testAddFieldDefinitionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddFieldDefinitionEventInterface::class,
            AddFieldDefinitionEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinitionCreateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->addFieldDefinition(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAddFieldDefinitionEventInterface::class, 0],
            [AddFieldDefinitionEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAddFieldDefinitionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddFieldDefinitionEventInterface::class,
            AddFieldDefinitionEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinitionCreateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeAddFieldDefinitionEventInterface::class, function (BeforeAddFieldDefinitionEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->addFieldDefinition(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAddFieldDefinitionEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AddFieldDefinitionEventInterface::class, 0],
            [BeforeAddFieldDefinitionEventInterface::class, 0],
        ]);
    }

    public function testDeleteContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentTypeGroupEventInterface::class,
            DeleteContentTypeGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteContentTypeGroupEventInterface::class, 0],
            [DeleteContentTypeGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentTypeGroupEventInterface::class,
            DeleteContentTypeGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteContentTypeGroupEventInterface::class, function (BeforeDeleteContentTypeGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteContentTypeGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteContentTypeGroupEventInterface::class, 0],
            [DeleteContentTypeGroupEventInterface::class, 0],
        ]);
    }

    public function testCreateContentTypeDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeDraftEventInterface::class,
            CreateContentTypeDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeDraft')->willReturn($contentTypeDraft);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($contentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeDraftEventInterface::class, 0],
            [CreateContentTypeDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentTypeDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeDraftEventInterface::class,
            CreateContentTypeDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeDraft')->willReturn($contentTypeDraft);

        $traceableEventDispatcher->addListener(BeforeCreateContentTypeDraftEventInterface::class, function (BeforeCreateContentTypeDraftEventInterface $event) use ($eventContentTypeDraft) {
            $event->setContentTypeDraft($eventContentTypeDraft);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeDraftEventInterface::class, 10],
            [BeforeCreateContentTypeDraftEventInterface::class, 0],
            [CreateContentTypeDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentTypeDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeDraftEventInterface::class,
            CreateContentTypeDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeDraft')->willReturn($contentTypeDraft);

        $traceableEventDispatcher->addListener(BeforeCreateContentTypeDraftEventInterface::class, function (BeforeCreateContentTypeDraftEventInterface $event) use ($eventContentTypeDraft) {
            $event->setContentTypeDraft($eventContentTypeDraft);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeDraftEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateContentTypeDraftEventInterface::class, 0],
            [CreateContentTypeDraftEventInterface::class, 0],
        ]);
    }

    public function testCreateContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeGroupEventInterface::class,
            CreateContentTypeGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeGroupCreateStruct::class),
        ];

        $contentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeGroup')->willReturn($contentTypeGroup);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($contentTypeGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeGroupEventInterface::class, 0],
            [CreateContentTypeGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentTypeGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeGroupEventInterface::class,
            CreateContentTypeGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeGroupCreateStruct::class),
        ];

        $contentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $eventContentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeGroup')->willReturn($contentTypeGroup);

        $traceableEventDispatcher->addListener(BeforeCreateContentTypeGroupEventInterface::class, function (BeforeCreateContentTypeGroupEventInterface $event) use ($eventContentTypeGroup) {
            $event->setContentTypeGroup($eventContentTypeGroup);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentTypeGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeGroupEventInterface::class, 10],
            [BeforeCreateContentTypeGroupEventInterface::class, 0],
            [CreateContentTypeGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeGroupEventInterface::class,
            CreateContentTypeGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeGroupCreateStruct::class),
        ];

        $contentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $eventContentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeGroup')->willReturn($contentTypeGroup);

        $traceableEventDispatcher->addListener(BeforeCreateContentTypeGroupEventInterface::class, function (BeforeCreateContentTypeGroupEventInterface $event) use ($eventContentTypeGroup) {
            $event->setContentTypeGroup($eventContentTypeGroup);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentTypeGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateContentTypeGroupEventInterface::class, 0],
            [CreateContentTypeGroupEventInterface::class, 0],
        ]);
    }

    public function testUpdateContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentTypeGroupEventInterface::class,
            UpdateContentTypeGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeGroup::class),
            $this->createMock(ContentTypeGroupUpdateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->updateContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUpdateContentTypeGroupEventInterface::class, 0],
            [UpdateContentTypeGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentTypeGroupEventInterface::class,
            UpdateContentTypeGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeGroup::class),
            $this->createMock(ContentTypeGroupUpdateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeUpdateContentTypeGroupEventInterface::class, function (BeforeUpdateContentTypeGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->updateContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUpdateContentTypeGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateContentTypeGroupEventInterface::class, 0],
            [UpdateContentTypeGroupEventInterface::class, 0],
        ]);
    }

    public function testCreateContentTypeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeEventInterface::class,
            CreateContentTypeEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeCreateStruct::class),
            [],
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentType')->willReturn($contentTypeDraft);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($contentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeEventInterface::class, 0],
            [CreateContentTypeEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentTypeResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeEventInterface::class,
            CreateContentTypeEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeCreateStruct::class),
            [],
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentType')->willReturn($contentTypeDraft);

        $traceableEventDispatcher->addListener(BeforeCreateContentTypeEventInterface::class, function (BeforeCreateContentTypeEventInterface $event) use ($eventContentTypeDraft) {
            $event->setContentTypeDraft($eventContentTypeDraft);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeEventInterface::class, 10],
            [BeforeCreateContentTypeEventInterface::class, 0],
            [CreateContentTypeEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentTypeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeEventInterface::class,
            CreateContentTypeEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeCreateStruct::class),
            [],
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentType')->willReturn($contentTypeDraft);

        $traceableEventDispatcher->addListener(BeforeCreateContentTypeEventInterface::class, function (BeforeCreateContentTypeEventInterface $event) use ($eventContentTypeDraft) {
            $event->setContentTypeDraft($eventContentTypeDraft);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateContentTypeEventInterface::class, 0],
            [CreateContentTypeEventInterface::class, 0],
        ]);
    }

    public function testRemoveContentTypeTranslationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveContentTypeTranslationEventInterface::class,
            RemoveContentTypeTranslationEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            'random_value_5cff79c318f864.57583321',
        ];

        $newContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('removeContentTypeTranslation')->willReturn($newContentTypeDraft);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->removeContentTypeTranslation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($newContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeRemoveContentTypeTranslationEventInterface::class, 0],
            [RemoveContentTypeTranslationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnRemoveContentTypeTranslationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveContentTypeTranslationEventInterface::class,
            RemoveContentTypeTranslationEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            'random_value_5cff79c318f913.11826610',
        ];

        $newContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventNewContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('removeContentTypeTranslation')->willReturn($newContentTypeDraft);

        $traceableEventDispatcher->addListener(BeforeRemoveContentTypeTranslationEventInterface::class, function (BeforeRemoveContentTypeTranslationEventInterface $event) use ($eventNewContentTypeDraft) {
            $event->setNewContentTypeDraft($eventNewContentTypeDraft);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->removeContentTypeTranslation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventNewContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeRemoveContentTypeTranslationEventInterface::class, 10],
            [BeforeRemoveContentTypeTranslationEventInterface::class, 0],
            [RemoveContentTypeTranslationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRemoveContentTypeTranslationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveContentTypeTranslationEventInterface::class,
            RemoveContentTypeTranslationEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            'random_value_5cff79c318f983.61112462',
        ];

        $newContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventNewContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('removeContentTypeTranslation')->willReturn($newContentTypeDraft);

        $traceableEventDispatcher->addListener(BeforeRemoveContentTypeTranslationEventInterface::class, function (BeforeRemoveContentTypeTranslationEventInterface $event) use ($eventNewContentTypeDraft) {
            $event->setNewContentTypeDraft($eventNewContentTypeDraft);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->removeContentTypeTranslation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventNewContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeRemoveContentTypeTranslationEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeRemoveContentTypeTranslationEventInterface::class, 0],
            [RemoveContentTypeTranslationEventInterface::class, 0],
        ]);
    }

    public function testUnassignContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnassignContentTypeGroupEventInterface::class,
            UnassignContentTypeGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->unassignContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUnassignContentTypeGroupEventInterface::class, 0],
            [UnassignContentTypeGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUnassignContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnassignContentTypeGroupEventInterface::class,
            UnassignContentTypeGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeUnassignContentTypeGroupEventInterface::class, function (BeforeUnassignContentTypeGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->unassignContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUnassignContentTypeGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUnassignContentTypeGroupEventInterface::class, 0],
            [UnassignContentTypeGroupEventInterface::class, 0],
        ]);
    }

    public function testPublishContentTypeDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforePublishContentTypeDraftEventInterface::class,
            PublishContentTypeDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->publishContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforePublishContentTypeDraftEventInterface::class, 0],
            [PublishContentTypeDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testPublishContentTypeDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforePublishContentTypeDraftEventInterface::class,
            PublishContentTypeDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforePublishContentTypeDraftEventInterface::class, function (BeforePublishContentTypeDraftEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->publishContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforePublishContentTypeDraftEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforePublishContentTypeDraftEventInterface::class, 0],
            [PublishContentTypeDraftEventInterface::class, 0],
        ]);
    }

    public function testUpdateFieldDefinitionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateFieldDefinitionEventInterface::class,
            UpdateFieldDefinitionEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinition::class),
            $this->createMock(FieldDefinitionUpdateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->updateFieldDefinition(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUpdateFieldDefinitionEventInterface::class, 0],
            [UpdateFieldDefinitionEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateFieldDefinitionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateFieldDefinitionEventInterface::class,
            UpdateFieldDefinitionEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinition::class),
            $this->createMock(FieldDefinitionUpdateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeUpdateFieldDefinitionEventInterface::class, function (BeforeUpdateFieldDefinitionEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->updateFieldDefinition(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUpdateFieldDefinitionEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateFieldDefinitionEventInterface::class, 0],
            [UpdateFieldDefinitionEventInterface::class, 0],
        ]);
    }

    public function testRemoveFieldDefinitionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveFieldDefinitionEventInterface::class,
            RemoveFieldDefinitionEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinition::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->removeFieldDefinition(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeRemoveFieldDefinitionEventInterface::class, 0],
            [RemoveFieldDefinitionEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRemoveFieldDefinitionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveFieldDefinitionEventInterface::class,
            RemoveFieldDefinitionEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinition::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeRemoveFieldDefinitionEventInterface::class, function (BeforeRemoveFieldDefinitionEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->removeFieldDefinition(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeRemoveFieldDefinitionEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeRemoveFieldDefinitionEventInterface::class, 0],
            [RemoveFieldDefinitionEventInterface::class, 0],
        ]);
    }

    public function testAssignContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignContentTypeGroupEventInterface::class,
            AssignContentTypeGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->assignContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAssignContentTypeGroupEventInterface::class, 0],
            [AssignContentTypeGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignContentTypeGroupEventInterface::class,
            AssignContentTypeGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeAssignContentTypeGroupEventInterface::class, function (BeforeAssignContentTypeGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->assignContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAssignContentTypeGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AssignContentTypeGroupEventInterface::class, 0],
            [BeforeAssignContentTypeGroupEventInterface::class, 0],
        ]);
    }

    public function testUpdateContentTypeDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentTypeDraftEventInterface::class,
            UpdateContentTypeDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(ContentTypeUpdateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->updateContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUpdateContentTypeDraftEventInterface::class, 0],
            [UpdateContentTypeDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateContentTypeDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentTypeDraftEventInterface::class,
            UpdateContentTypeDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(ContentTypeUpdateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeUpdateContentTypeDraftEventInterface::class, function (BeforeUpdateContentTypeDraftEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->updateContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUpdateContentTypeDraftEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateContentTypeDraftEventInterface::class, 0],
            [UpdateContentTypeDraftEventInterface::class, 0],
        ]);
    }

    public function testDeleteContentTypeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentTypeEventInterface::class,
            DeleteContentTypeEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteContentTypeEventInterface::class, 0],
            [DeleteContentTypeEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteContentTypeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentTypeEventInterface::class,
            DeleteContentTypeEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteContentTypeEventInterface::class, function (BeforeDeleteContentTypeEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteContentTypeEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteContentTypeEventInterface::class, 0],
            [DeleteContentTypeEventInterface::class, 0],
        ]);
    }

    public function testCopyContentTypeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopyContentTypeEventInterface::class,
            CopyContentTypeEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(User::class),
        ];

        $contentTypeCopy = $this->createMock(ContentType::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('copyContentType')->willReturn($contentTypeCopy);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($contentTypeCopy, $result);
        $this->assertSame($calledListeners, [
            [BeforeCopyContentTypeEventInterface::class, 0],
            [CopyContentTypeEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCopyContentTypeResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopyContentTypeEventInterface::class,
            CopyContentTypeEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(User::class),
        ];

        $contentTypeCopy = $this->createMock(ContentType::class);
        $eventContentTypeCopy = $this->createMock(ContentType::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('copyContentType')->willReturn($contentTypeCopy);

        $traceableEventDispatcher->addListener(BeforeCopyContentTypeEventInterface::class, function (BeforeCopyContentTypeEventInterface $event) use ($eventContentTypeCopy) {
            $event->setContentTypeCopy($eventContentTypeCopy);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentTypeCopy, $result);
        $this->assertSame($calledListeners, [
            [BeforeCopyContentTypeEventInterface::class, 10],
            [BeforeCopyContentTypeEventInterface::class, 0],
            [CopyContentTypeEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCopyContentTypeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopyContentTypeEventInterface::class,
            CopyContentTypeEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(User::class),
        ];

        $contentTypeCopy = $this->createMock(ContentType::class);
        $eventContentTypeCopy = $this->createMock(ContentType::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('copyContentType')->willReturn($contentTypeCopy);

        $traceableEventDispatcher->addListener(BeforeCopyContentTypeEventInterface::class, function (BeforeCopyContentTypeEventInterface $event) use ($eventContentTypeCopy) {
            $event->setContentTypeCopy($eventContentTypeCopy);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentTypeCopy, $result);
        $this->assertSame($calledListeners, [
            [BeforeCopyContentTypeEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCopyContentTypeEventInterface::class, 0],
            [CopyContentTypeEventInterface::class, 0],
        ]);
    }
}
