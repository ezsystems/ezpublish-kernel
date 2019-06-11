<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
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
use eZ\Publish\Core\Event\ContentTypeService;
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
use eZ\Publish\Core\Event\ContentType\ContentTypeEvents;

class ContentTypeServiceTest extends AbstractServiceTest
{
    public function testAddFieldDefinitionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_ADD_FIELD_DEFINITION,
            ContentTypeEvents::ADD_FIELD_DEFINITION
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
            [ContentTypeEvents::BEFORE_ADD_FIELD_DEFINITION, 0],
            [ContentTypeEvents::ADD_FIELD_DEFINITION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAddFieldDefinitionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_ADD_FIELD_DEFINITION,
            ContentTypeEvents::ADD_FIELD_DEFINITION
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinitionCreateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_ADD_FIELD_DEFINITION, function (BeforeAddFieldDefinitionEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->addFieldDefinition(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_ADD_FIELD_DEFINITION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::ADD_FIELD_DEFINITION, 0],
            [ContentTypeEvents::BEFORE_ADD_FIELD_DEFINITION, 0],
        ]);
    }

    public function testDeleteContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE_GROUP,
            ContentTypeEvents::DELETE_CONTENT_TYPE_GROUP
        );

        $parameters = [
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE_GROUP, 0],
            [ContentTypeEvents::DELETE_CONTENT_TYPE_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE_GROUP,
            ContentTypeEvents::DELETE_CONTENT_TYPE_GROUP
        );

        $parameters = [
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE_GROUP, function (BeforeDeleteContentTypeGroupEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::DELETE_CONTENT_TYPE_GROUP, 0],
            [ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE_GROUP, 0],
        ]);
    }

    public function testCreateContentTypeDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_DRAFT,
            ContentTypeEvents::CREATE_CONTENT_TYPE_DRAFT
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
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_DRAFT, 0],
            [ContentTypeEvents::CREATE_CONTENT_TYPE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentTypeDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_DRAFT,
            ContentTypeEvents::CREATE_CONTENT_TYPE_DRAFT
        );

        $parameters = [
            $this->createMock(ContentType::class),
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeDraft')->willReturn($contentTypeDraft);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_DRAFT, function (BeforeCreateContentTypeDraftEvent $event) use ($eventContentTypeDraft) {
            $event->setContentTypeDraft($eventContentTypeDraft);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_DRAFT, 10],
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_DRAFT, 0],
            [ContentTypeEvents::CREATE_CONTENT_TYPE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentTypeDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_DRAFT,
            ContentTypeEvents::CREATE_CONTENT_TYPE_DRAFT
        );

        $parameters = [
            $this->createMock(ContentType::class),
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeDraft')->willReturn($contentTypeDraft);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_DRAFT, function (BeforeCreateContentTypeDraftEvent $event) use ($eventContentTypeDraft) {
            $event->setContentTypeDraft($eventContentTypeDraft);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_DRAFT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::CREATE_CONTENT_TYPE_DRAFT, 0],
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_DRAFT, 0],
        ]);
    }

    public function testCreateContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_GROUP,
            ContentTypeEvents::CREATE_CONTENT_TYPE_GROUP
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
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_GROUP, 0],
            [ContentTypeEvents::CREATE_CONTENT_TYPE_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentTypeGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_GROUP,
            ContentTypeEvents::CREATE_CONTENT_TYPE_GROUP
        );

        $parameters = [
            $this->createMock(ContentTypeGroupCreateStruct::class),
        ];

        $contentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $eventContentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeGroup')->willReturn($contentTypeGroup);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_GROUP, function (BeforeCreateContentTypeGroupEvent $event) use ($eventContentTypeGroup) {
            $event->setContentTypeGroup($eventContentTypeGroup);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentTypeGroup, $result);
        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_GROUP, 10],
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_GROUP, 0],
            [ContentTypeEvents::CREATE_CONTENT_TYPE_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_GROUP,
            ContentTypeEvents::CREATE_CONTENT_TYPE_GROUP
        );

        $parameters = [
            $this->createMock(ContentTypeGroupCreateStruct::class),
        ];

        $contentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $eventContentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentTypeGroup')->willReturn($contentTypeGroup);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_GROUP, function (BeforeCreateContentTypeGroupEvent $event) use ($eventContentTypeGroup) {
            $event->setContentTypeGroup($eventContentTypeGroup);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentTypeGroup, $result);
        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::CREATE_CONTENT_TYPE_GROUP, 0],
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_GROUP, 0],
        ]);
    }

    public function testUpdateContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_GROUP,
            ContentTypeEvents::UPDATE_CONTENT_TYPE_GROUP
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
            [ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_GROUP, 0],
            [ContentTypeEvents::UPDATE_CONTENT_TYPE_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_GROUP,
            ContentTypeEvents::UPDATE_CONTENT_TYPE_GROUP
        );

        $parameters = [
            $this->createMock(ContentTypeGroup::class),
            $this->createMock(ContentTypeGroupUpdateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_GROUP, function (BeforeUpdateContentTypeGroupEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->updateContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::UPDATE_CONTENT_TYPE_GROUP, 0],
            [ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_GROUP, 0],
        ]);
    }

    public function testCreateContentTypeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE,
            ContentTypeEvents::CREATE_CONTENT_TYPE
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
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE, 0],
            [ContentTypeEvents::CREATE_CONTENT_TYPE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentTypeResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE,
            ContentTypeEvents::CREATE_CONTENT_TYPE
        );

        $parameters = [
            $this->createMock(ContentTypeCreateStruct::class),
            [],
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentType')->willReturn($contentTypeDraft);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE, function (BeforeCreateContentTypeEvent $event) use ($eventContentTypeDraft) {
            $event->setContentTypeDraft($eventContentTypeDraft);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE, 10],
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE, 0],
            [ContentTypeEvents::CREATE_CONTENT_TYPE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentTypeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE,
            ContentTypeEvents::CREATE_CONTENT_TYPE
        );

        $parameters = [
            $this->createMock(ContentTypeCreateStruct::class),
            [],
        ];

        $contentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('createContentType')->willReturn($contentTypeDraft);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE, function (BeforeCreateContentTypeEvent $event) use ($eventContentTypeDraft) {
            $event->setContentTypeDraft($eventContentTypeDraft);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::CREATE_CONTENT_TYPE, 0],
            [ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE, 0],
        ]);
    }

    public function testRemoveContentTypeTranslationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_REMOVE_CONTENT_TYPE_TRANSLATION,
            ContentTypeEvents::REMOVE_CONTENT_TYPE_TRANSLATION
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
            [ContentTypeEvents::BEFORE_REMOVE_CONTENT_TYPE_TRANSLATION, 0],
            [ContentTypeEvents::REMOVE_CONTENT_TYPE_TRANSLATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnRemoveContentTypeTranslationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_REMOVE_CONTENT_TYPE_TRANSLATION,
            ContentTypeEvents::REMOVE_CONTENT_TYPE_TRANSLATION
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            'random_value_5cff79c318f913.11826610',
        ];

        $newContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventNewContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('removeContentTypeTranslation')->willReturn($newContentTypeDraft);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_REMOVE_CONTENT_TYPE_TRANSLATION, function (BeforeRemoveContentTypeTranslationEvent $event) use ($eventNewContentTypeDraft) {
            $event->setNewContentTypeDraft($eventNewContentTypeDraft);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->removeContentTypeTranslation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventNewContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_REMOVE_CONTENT_TYPE_TRANSLATION, 10],
            [ContentTypeEvents::BEFORE_REMOVE_CONTENT_TYPE_TRANSLATION, 0],
            [ContentTypeEvents::REMOVE_CONTENT_TYPE_TRANSLATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRemoveContentTypeTranslationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_REMOVE_CONTENT_TYPE_TRANSLATION,
            ContentTypeEvents::REMOVE_CONTENT_TYPE_TRANSLATION
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            'random_value_5cff79c318f983.61112462',
        ];

        $newContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $eventNewContentTypeDraft = $this->createMock(ContentTypeDraft::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('removeContentTypeTranslation')->willReturn($newContentTypeDraft);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_REMOVE_CONTENT_TYPE_TRANSLATION, function (BeforeRemoveContentTypeTranslationEvent $event) use ($eventNewContentTypeDraft) {
            $event->setNewContentTypeDraft($eventNewContentTypeDraft);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->removeContentTypeTranslation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventNewContentTypeDraft, $result);
        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_REMOVE_CONTENT_TYPE_TRANSLATION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::REMOVE_CONTENT_TYPE_TRANSLATION, 0],
            [ContentTypeEvents::BEFORE_REMOVE_CONTENT_TYPE_TRANSLATION, 0],
        ]);
    }

    public function testUnassignContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_UNASSIGN_CONTENT_TYPE_GROUP,
            ContentTypeEvents::UNASSIGN_CONTENT_TYPE_GROUP
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
            [ContentTypeEvents::BEFORE_UNASSIGN_CONTENT_TYPE_GROUP, 0],
            [ContentTypeEvents::UNASSIGN_CONTENT_TYPE_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUnassignContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_UNASSIGN_CONTENT_TYPE_GROUP,
            ContentTypeEvents::UNASSIGN_CONTENT_TYPE_GROUP
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_UNASSIGN_CONTENT_TYPE_GROUP, function (BeforeUnassignContentTypeGroupEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->unassignContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_UNASSIGN_CONTENT_TYPE_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::UNASSIGN_CONTENT_TYPE_GROUP, 0],
            [ContentTypeEvents::BEFORE_UNASSIGN_CONTENT_TYPE_GROUP, 0],
        ]);
    }

    public function testPublishContentTypeDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_PUBLISH_CONTENT_TYPE_DRAFT,
            ContentTypeEvents::PUBLISH_CONTENT_TYPE_DRAFT
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->publishContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_PUBLISH_CONTENT_TYPE_DRAFT, 0],
            [ContentTypeEvents::PUBLISH_CONTENT_TYPE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testPublishContentTypeDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_PUBLISH_CONTENT_TYPE_DRAFT,
            ContentTypeEvents::PUBLISH_CONTENT_TYPE_DRAFT
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_PUBLISH_CONTENT_TYPE_DRAFT, function (BeforePublishContentTypeDraftEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->publishContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_PUBLISH_CONTENT_TYPE_DRAFT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::PUBLISH_CONTENT_TYPE_DRAFT, 0],
            [ContentTypeEvents::BEFORE_PUBLISH_CONTENT_TYPE_DRAFT, 0],
        ]);
    }

    public function testUpdateFieldDefinitionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_UPDATE_FIELD_DEFINITION,
            ContentTypeEvents::UPDATE_FIELD_DEFINITION
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
            [ContentTypeEvents::BEFORE_UPDATE_FIELD_DEFINITION, 0],
            [ContentTypeEvents::UPDATE_FIELD_DEFINITION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateFieldDefinitionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_UPDATE_FIELD_DEFINITION,
            ContentTypeEvents::UPDATE_FIELD_DEFINITION
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinition::class),
            $this->createMock(FieldDefinitionUpdateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_UPDATE_FIELD_DEFINITION, function (BeforeUpdateFieldDefinitionEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->updateFieldDefinition(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_UPDATE_FIELD_DEFINITION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::UPDATE_FIELD_DEFINITION, 0],
            [ContentTypeEvents::BEFORE_UPDATE_FIELD_DEFINITION, 0],
        ]);
    }

    public function testRemoveFieldDefinitionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_REMOVE_FIELD_DEFINITION,
            ContentTypeEvents::REMOVE_FIELD_DEFINITION
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
            [ContentTypeEvents::BEFORE_REMOVE_FIELD_DEFINITION, 0],
            [ContentTypeEvents::REMOVE_FIELD_DEFINITION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRemoveFieldDefinitionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_REMOVE_FIELD_DEFINITION,
            ContentTypeEvents::REMOVE_FIELD_DEFINITION
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(FieldDefinition::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_REMOVE_FIELD_DEFINITION, function (BeforeRemoveFieldDefinitionEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->removeFieldDefinition(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_REMOVE_FIELD_DEFINITION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::REMOVE_FIELD_DEFINITION, 0],
            [ContentTypeEvents::BEFORE_REMOVE_FIELD_DEFINITION, 0],
        ]);
    }

    public function testAssignContentTypeGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_ASSIGN_CONTENT_TYPE_GROUP,
            ContentTypeEvents::ASSIGN_CONTENT_TYPE_GROUP
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
            [ContentTypeEvents::BEFORE_ASSIGN_CONTENT_TYPE_GROUP, 0],
            [ContentTypeEvents::ASSIGN_CONTENT_TYPE_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignContentTypeGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_ASSIGN_CONTENT_TYPE_GROUP,
            ContentTypeEvents::ASSIGN_CONTENT_TYPE_GROUP
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(ContentTypeGroup::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_ASSIGN_CONTENT_TYPE_GROUP, function (BeforeAssignContentTypeGroupEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->assignContentTypeGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_ASSIGN_CONTENT_TYPE_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::ASSIGN_CONTENT_TYPE_GROUP, 0],
            [ContentTypeEvents::BEFORE_ASSIGN_CONTENT_TYPE_GROUP, 0],
        ]);
    }

    public function testUpdateContentTypeDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_DRAFT,
            ContentTypeEvents::UPDATE_CONTENT_TYPE_DRAFT
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
            [ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_DRAFT, 0],
            [ContentTypeEvents::UPDATE_CONTENT_TYPE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateContentTypeDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_DRAFT,
            ContentTypeEvents::UPDATE_CONTENT_TYPE_DRAFT
        );

        $parameters = [
            $this->createMock(ContentTypeDraft::class),
            $this->createMock(ContentTypeUpdateStruct::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_DRAFT, function (BeforeUpdateContentTypeDraftEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->updateContentTypeDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_DRAFT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::UPDATE_CONTENT_TYPE_DRAFT, 0],
            [ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_DRAFT, 0],
        ]);
    }

    public function testDeleteContentTypeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE,
            ContentTypeEvents::DELETE_CONTENT_TYPE
        );

        $parameters = [
            $this->createMock(ContentType::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE, 0],
            [ContentTypeEvents::DELETE_CONTENT_TYPE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteContentTypeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE,
            ContentTypeEvents::DELETE_CONTENT_TYPE
        );

        $parameters = [
            $this->createMock(ContentType::class),
        ];

        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE, function (BeforeDeleteContentTypeEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::DELETE_CONTENT_TYPE, 0],
            [ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE, 0],
        ]);
    }

    public function testCopyContentTypeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_COPY_CONTENT_TYPE,
            ContentTypeEvents::COPY_CONTENT_TYPE
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
            [ContentTypeEvents::BEFORE_COPY_CONTENT_TYPE, 0],
            [ContentTypeEvents::COPY_CONTENT_TYPE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCopyContentTypeResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_COPY_CONTENT_TYPE,
            ContentTypeEvents::COPY_CONTENT_TYPE
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(User::class),
        ];

        $contentTypeCopy = $this->createMock(ContentType::class);
        $eventContentTypeCopy = $this->createMock(ContentType::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('copyContentType')->willReturn($contentTypeCopy);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_COPY_CONTENT_TYPE, function (BeforeCopyContentTypeEvent $event) use ($eventContentTypeCopy) {
            $event->setContentTypeCopy($eventContentTypeCopy);
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentTypeCopy, $result);
        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_COPY_CONTENT_TYPE, 10],
            [ContentTypeEvents::BEFORE_COPY_CONTENT_TYPE, 0],
            [ContentTypeEvents::COPY_CONTENT_TYPE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCopyContentTypeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentTypeEvents::BEFORE_COPY_CONTENT_TYPE,
            ContentTypeEvents::COPY_CONTENT_TYPE
        );

        $parameters = [
            $this->createMock(ContentType::class),
            $this->createMock(User::class),
        ];

        $contentTypeCopy = $this->createMock(ContentType::class);
        $eventContentTypeCopy = $this->createMock(ContentType::class);
        $innerServiceMock = $this->createMock(ContentTypeServiceInterface::class);
        $innerServiceMock->method('copyContentType')->willReturn($contentTypeCopy);

        $traceableEventDispatcher->addListener(ContentTypeEvents::BEFORE_COPY_CONTENT_TYPE, function (BeforeCopyContentTypeEvent $event) use ($eventContentTypeCopy) {
            $event->setContentTypeCopy($eventContentTypeCopy);
            $event->stopPropagation();
        }, 10);

        $service = new ContentTypeService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContentType(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentTypeCopy, $result);
        $this->assertSame($calledListeners, [
            [ContentTypeEvents::BEFORE_COPY_CONTENT_TYPE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentTypeEvents::COPY_CONTENT_TYPE, 0],
            [ContentTypeEvents::BEFORE_COPY_CONTENT_TYPE, 0],
        ]);
    }
}
