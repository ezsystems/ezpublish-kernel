<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
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
use eZ\Publish\Core\Event\ContentType\AddFieldDefinitionEvent;
use eZ\Publish\Core\Event\ContentType\AssignContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\BeforeAddFieldDefinitionEvent;
use eZ\Publish\Core\Event\ContentType\BeforeAssignContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\BeforeCopyContentTypeEvent;
use eZ\Publish\Core\Event\ContentType\BeforeCreateContentTypeDraftEvent;
use eZ\Publish\Core\Event\ContentType\BeforeCreateContentTypeEvent;
use eZ\Publish\Core\Event\ContentType\BeforeCreateContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\BeforeDeleteContentTypeEvent;
use eZ\Publish\Core\Event\ContentType\BeforeDeleteContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\BeforePublishContentTypeDraftEvent;
use eZ\Publish\Core\Event\ContentType\BeforeRemoveContentTypeTranslationEvent;
use eZ\Publish\Core\Event\ContentType\BeforeRemoveFieldDefinitionEvent;
use eZ\Publish\Core\Event\ContentType\BeforeUnassignContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\BeforeUpdateContentTypeDraftEvent;
use eZ\Publish\Core\Event\ContentType\BeforeUpdateContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\BeforeUpdateFieldDefinitionEvent;
use eZ\Publish\Core\Event\ContentType\CopyContentTypeEvent;
use eZ\Publish\Core\Event\ContentType\CreateContentTypeDraftEvent;
use eZ\Publish\Core\Event\ContentType\CreateContentTypeEvent;
use eZ\Publish\Core\Event\ContentType\CreateContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\DeleteContentTypeEvent;
use eZ\Publish\Core\Event\ContentType\DeleteContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\PublishContentTypeDraftEvent;
use eZ\Publish\Core\Event\ContentType\RemoveContentTypeTranslationEvent;
use eZ\Publish\Core\Event\ContentType\RemoveFieldDefinitionEvent;
use eZ\Publish\Core\Event\ContentType\UnassignContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\UpdateContentTypeDraftEvent;
use eZ\Publish\Core\Event\ContentType\UpdateContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\UpdateFieldDefinitionEvent;
use eZ\Publish\Core\Event\ContentTypeService;

class ContentTypeServiceTest extends AbstractServiceTest
{
    public function testAddFieldDefinitionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddFieldDefinitionEvent::class,
            AddFieldDefinitionEvent::class
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
            [BeforeAddFieldDefinitionEvent::class, 0],
            [AddFieldDefinitionEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAddFieldDefinitionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddFieldDefinitionEvent::class,
            AddFieldDefinitionEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinitionCreateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeAddFieldDefinitionEvent::class, function (BeforeAddFieldDefinitionEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->addFieldDefinition(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAddFieldDefinitionEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AddFieldDefinitionEvent::class, 0],
            [BeforeAddFieldDefinitionEvent::class, 0],
        ]);
    }

    public function testDeleteContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentTypeGroupEvent::class,
            DeleteContentTypeGroupEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteContentTypeGroupEvent::class, 0],
            [DeleteContentTypeGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentTypeGroupEvent::class,
            DeleteContentTypeGroupEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteContentTypeGroupEvent::class, function (BeforeDeleteContentTypeGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteContentTypeGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteContentTypeGroupEvent::class, 0],
            [DeleteContentTypeGroupEvent::class, 0],
        ]);
    }

    public function testCreateContentTypeDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeDraftEvent::class,
            CreateContentTypeDraftEvent::class
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
            [BeforeCreateContentTypeDraftEvent::class, 0],
            [CreateContentTypeDraftEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentTypeDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeDraftEvent::class,
            CreateContentTypeDraftEvent::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeDraft')->willReturn($contentTypeDraft);

        $traceableEventDispatcher->addListener(BeforeCreateContentTypeDraftEvent::class, function (BeforeCreateContentTypeDraftEventInterface $event) use ($eventContentTypeDraft) {
            $event->setContentTypeDraft($eventContentTypeDraft);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeDraftEvent::class, 10],
            [BeforeCreateContentTypeDraftEvent::class, 0],
            [CreateContentTypeDraftEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentTypeDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeDraftEvent::class,
            CreateContentTypeDraftEvent::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeDraft')->willReturn($contentTypeDraft);

        $traceableEventDispatcher->addListener(BeforeCreateContentTypeDraftEvent::class, function (BeforeCreateContentTypeDraftEventInterface $event) use ($eventContentTypeDraft) {
            $event->setContentTypeDraft($eventContentTypeDraft);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeDraftEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateContentTypeDraftEvent::class, 0],
            [CreateContentTypeDraftEvent::class, 0],
        ]);
    }

    public function testCreateContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeGroupEvent::class,
            CreateContentTypeGroupEvent::class
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
            [BeforeCreateContentTypeGroupEvent::class, 0],
            [CreateContentTypeGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentTypeGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeGroupEvent::class,
            CreateContentTypeGroupEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeGroupCreateStruct::class),
        ];

        $contentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $eventContentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeGroup')->willReturn($contentTypeGroup);

        $traceableEventDispatcher->addListener(BeforeCreateContentTypeGroupEvent::class, function (BeforeCreateContentTypeGroupEventInterface $event) use ($eventContentTypeGroup) {
            $event->setContentTypeGroup($eventContentTypeGroup);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentTypeGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeGroupEvent::class, 10],
            [BeforeCreateContentTypeGroupEvent::class, 0],
            [CreateContentTypeGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeGroupEvent::class,
            CreateContentTypeGroupEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeGroupCreateStruct::class),
        ];

        $contentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $eventContentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeGroup')->willReturn($contentTypeGroup);

        $traceableEventDispatcher->addListener(BeforeCreateContentTypeGroupEvent::class, function (BeforeCreateContentTypeGroupEventInterface $event) use ($eventContentTypeGroup) {
            $event->setContentTypeGroup($eventContentTypeGroup);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentTypeGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateContentTypeGroupEvent::class, 0],
            [CreateContentTypeGroupEvent::class, 0],
        ]);
    }

    public function testUpdateContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentTypeGroupEvent::class,
            UpdateContentTypeGroupEvent::class
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
            [BeforeUpdateContentTypeGroupEvent::class, 0],
            [UpdateContentTypeGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentTypeGroupEvent::class,
            UpdateContentTypeGroupEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeGroup::class),
            $this->createMock(ContentTypeGroupUpdateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeUpdateContentTypeGroupEvent::class, function (BeforeUpdateContentTypeGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->updateContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUpdateContentTypeGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateContentTypeGroupEvent::class, 0],
            [UpdateContentTypeGroupEvent::class, 0],
        ]);
    }

    public function testCreateContentTypeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeEvent::class,
            CreateContentTypeEvent::class
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
            [BeforeCreateContentTypeEvent::class, 0],
            [CreateContentTypeEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentTypeResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeEvent::class,
            CreateContentTypeEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeCreateStruct::class),
            [],
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentType')->willReturn($contentTypeDraft);

        $traceableEventDispatcher->addListener(BeforeCreateContentTypeEvent::class, function (BeforeCreateContentTypeEventInterface $event) use ($eventContentTypeDraft) {
            $event->setContentTypeDraft($eventContentTypeDraft);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeEvent::class, 10],
            [BeforeCreateContentTypeEvent::class, 0],
            [CreateContentTypeEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentTypeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentTypeEvent::class,
            CreateContentTypeEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeCreateStruct::class),
            [],
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentType')->willReturn($contentTypeDraft);

        $traceableEventDispatcher->addListener(BeforeCreateContentTypeEvent::class, function (BeforeCreateContentTypeEventInterface $event) use ($eventContentTypeDraft) {
            $event->setContentTypeDraft($eventContentTypeDraft);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentTypeEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateContentTypeEvent::class, 0],
            [CreateContentTypeEvent::class, 0],
        ]);
    }

    public function testRemoveContentTypeTranslationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveContentTypeTranslationEvent::class,
            RemoveContentTypeTranslationEvent::class
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
            [BeforeRemoveContentTypeTranslationEvent::class, 0],
            [RemoveContentTypeTranslationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnRemoveContentTypeTranslationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveContentTypeTranslationEvent::class,
            RemoveContentTypeTranslationEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            'random_value_5cff79c318f913.11826610',
        ];

        $newContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventNewContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('removeContentTypeTranslation')->willReturn($newContentTypeDraft);

        $traceableEventDispatcher->addListener(BeforeRemoveContentTypeTranslationEvent::class, function (BeforeRemoveContentTypeTranslationEventInterface $event) use ($eventNewContentTypeDraft) {
            $event->setNewContentTypeDraft($eventNewContentTypeDraft);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->removeContentTypeTranslation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventNewContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeRemoveContentTypeTranslationEvent::class, 10],
            [BeforeRemoveContentTypeTranslationEvent::class, 0],
            [RemoveContentTypeTranslationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRemoveContentTypeTranslationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveContentTypeTranslationEvent::class,
            RemoveContentTypeTranslationEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            'random_value_5cff79c318f983.61112462',
        ];

        $newContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventNewContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('removeContentTypeTranslation')->willReturn($newContentTypeDraft);

        $traceableEventDispatcher->addListener(BeforeRemoveContentTypeTranslationEvent::class, function (BeforeRemoveContentTypeTranslationEventInterface $event) use ($eventNewContentTypeDraft) {
            $event->setNewContentTypeDraft($eventNewContentTypeDraft);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->removeContentTypeTranslation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventNewContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeRemoveContentTypeTranslationEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeRemoveContentTypeTranslationEvent::class, 0],
            [RemoveContentTypeTranslationEvent::class, 0],
        ]);
    }

    public function testUnassignContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnassignContentTypeGroupEvent::class,
            UnassignContentTypeGroupEvent::class
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
            [BeforeUnassignContentTypeGroupEvent::class, 0],
            [UnassignContentTypeGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUnassignContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnassignContentTypeGroupEvent::class,
            UnassignContentTypeGroupEvent::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeUnassignContentTypeGroupEvent::class, function (BeforeUnassignContentTypeGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->unassignContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUnassignContentTypeGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUnassignContentTypeGroupEvent::class, 0],
            [UnassignContentTypeGroupEvent::class, 0],
        ]);
    }

    public function testPublishContentTypeDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforePublishContentTypeDraftEvent::class,
            PublishContentTypeDraftEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->publishContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforePublishContentTypeDraftEvent::class, 0],
            [PublishContentTypeDraftEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testPublishContentTypeDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforePublishContentTypeDraftEvent::class,
            PublishContentTypeDraftEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforePublishContentTypeDraftEvent::class, function (BeforePublishContentTypeDraftEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->publishContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforePublishContentTypeDraftEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforePublishContentTypeDraftEvent::class, 0],
            [PublishContentTypeDraftEvent::class, 0],
        ]);
    }

    public function testUpdateFieldDefinitionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateFieldDefinitionEvent::class,
            UpdateFieldDefinitionEvent::class
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
            [BeforeUpdateFieldDefinitionEvent::class, 0],
            [UpdateFieldDefinitionEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateFieldDefinitionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateFieldDefinitionEvent::class,
            UpdateFieldDefinitionEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinition::class),
            $this->createMock(FieldDefinitionUpdateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeUpdateFieldDefinitionEvent::class, function (BeforeUpdateFieldDefinitionEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->updateFieldDefinition(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUpdateFieldDefinitionEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateFieldDefinitionEvent::class, 0],
            [UpdateFieldDefinitionEvent::class, 0],
        ]);
    }

    public function testRemoveFieldDefinitionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveFieldDefinitionEvent::class,
            RemoveFieldDefinitionEvent::class
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
            [BeforeRemoveFieldDefinitionEvent::class, 0],
            [RemoveFieldDefinitionEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRemoveFieldDefinitionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveFieldDefinitionEvent::class,
            RemoveFieldDefinitionEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinition::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeRemoveFieldDefinitionEvent::class, function (BeforeRemoveFieldDefinitionEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->removeFieldDefinition(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeRemoveFieldDefinitionEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeRemoveFieldDefinitionEvent::class, 0],
            [RemoveFieldDefinitionEvent::class, 0],
        ]);
    }

    public function testAssignContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignContentTypeGroupEvent::class,
            AssignContentTypeGroupEvent::class
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
            [BeforeAssignContentTypeGroupEvent::class, 0],
            [AssignContentTypeGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignContentTypeGroupEvent::class,
            AssignContentTypeGroupEvent::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeAssignContentTypeGroupEvent::class, function (BeforeAssignContentTypeGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->assignContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAssignContentTypeGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AssignContentTypeGroupEvent::class, 0],
            [BeforeAssignContentTypeGroupEvent::class, 0],
        ]);
    }

    public function testUpdateContentTypeDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentTypeDraftEvent::class,
            UpdateContentTypeDraftEvent::class
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
            [BeforeUpdateContentTypeDraftEvent::class, 0],
            [UpdateContentTypeDraftEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateContentTypeDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentTypeDraftEvent::class,
            UpdateContentTypeDraftEvent::class
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(ContentTypeUpdateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeUpdateContentTypeDraftEvent::class, function (BeforeUpdateContentTypeDraftEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->updateContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUpdateContentTypeDraftEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateContentTypeDraftEvent::class, 0],
            [UpdateContentTypeDraftEvent::class, 0],
        ]);
    }

    public function testDeleteContentTypeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentTypeEvent::class,
            DeleteContentTypeEvent::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteContentTypeEvent::class, 0],
            [DeleteContentTypeEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteContentTypeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentTypeEvent::class,
            DeleteContentTypeEvent::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteContentTypeEvent::class, function (BeforeDeleteContentTypeEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteContentTypeEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteContentTypeEvent::class, 0],
            [DeleteContentTypeEvent::class, 0],
        ]);
    }

    public function testCopyContentTypeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopyContentTypeEvent::class,
            CopyContentTypeEvent::class
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
            [BeforeCopyContentTypeEvent::class, 0],
            [CopyContentTypeEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCopyContentTypeResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopyContentTypeEvent::class,
            CopyContentTypeEvent::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(User::class),
        ];

        $contentTypeCopy = $this->createMock(ContentType::class);
        $eventContentTypeCopy = $this->createMock(ContentType::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('copyContentType')->willReturn($contentTypeCopy);

        $traceableEventDispatcher->addListener(BeforeCopyContentTypeEvent::class, function (BeforeCopyContentTypeEventInterface $event) use ($eventContentTypeCopy) {
            $event->setContentTypeCopy($eventContentTypeCopy);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentTypeCopy, $result);
        $this->assertSame($calledListeners, [
            [BeforeCopyContentTypeEvent::class, 10],
            [BeforeCopyContentTypeEvent::class, 0],
            [CopyContentTypeEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCopyContentTypeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopyContentTypeEvent::class,
            CopyContentTypeEvent::class
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(User::class),
        ];

        $contentTypeCopy = $this->createMock(ContentType::class);
        $eventContentTypeCopy = $this->createMock(ContentType::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('copyContentType')->willReturn($contentTypeCopy);

        $traceableEventDispatcher->addListener(BeforeCopyContentTypeEvent::class, function (BeforeCopyContentTypeEventInterface $event) use ($eventContentTypeCopy) {
            $event->setContentTypeCopy($eventContentTypeCopy);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentTypeCopy, $result);
        $this->assertSame($calledListeners, [
            [BeforeCopyContentTypeEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCopyContentTypeEvent::class, 0],
            [CopyContentTypeEvent::class, 0],
        ]);
    }
}
