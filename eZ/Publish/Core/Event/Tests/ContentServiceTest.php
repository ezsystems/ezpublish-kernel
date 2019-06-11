<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Event\ContentService;
use eZ\Publish\Core\Event\Content\BeforeAddRelationEvent;
use eZ\Publish\Core\Event\Content\BeforeCopyContentEvent;
use eZ\Publish\Core\Event\Content\BeforeCreateContentDraftEvent;
use eZ\Publish\Core\Event\Content\BeforeCreateContentEvent;
use eZ\Publish\Core\Event\Content\BeforeDeleteContentEvent;
use eZ\Publish\Core\Event\Content\BeforeDeleteRelationEvent;
use eZ\Publish\Core\Event\Content\BeforeDeleteTranslationEvent;
use eZ\Publish\Core\Event\Content\BeforeDeleteVersionEvent;
use eZ\Publish\Core\Event\Content\BeforeHideContentEvent;
use eZ\Publish\Core\Event\Content\BeforePublishVersionEvent;
use eZ\Publish\Core\Event\Content\BeforeRevealContentEvent;
use eZ\Publish\Core\Event\Content\BeforeUpdateContentEvent;
use eZ\Publish\Core\Event\Content\BeforeUpdateContentMetadataEvent;
use eZ\Publish\Core\Event\Content\ContentEvents;

class ContentServiceTest extends AbstractServiceTest
{
    public function testDeleteContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_DELETE_CONTENT,
            ContentEvents::DELETE_CONTENT
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $locations = [];
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('deleteContent')->willReturn($locations);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($locations, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_DELETE_CONTENT, 0],
            [ContentEvents::DELETE_CONTENT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnDeleteContentResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_DELETE_CONTENT,
            ContentEvents::DELETE_CONTENT
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('deleteContent')->willReturn($locations);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_DELETE_CONTENT, function (BeforeDeleteContentEvent $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_DELETE_CONTENT, 10],
            [ContentEvents::BEFORE_DELETE_CONTENT, 0],
            [ContentEvents::DELETE_CONTENT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_DELETE_CONTENT,
            ContentEvents::DELETE_CONTENT
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('deleteContent')->willReturn($locations);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_DELETE_CONTENT, function (BeforeDeleteContentEvent $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_DELETE_CONTENT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentEvents::DELETE_CONTENT, 0],
            [ContentEvents::BEFORE_DELETE_CONTENT, 0],
        ]);
    }

    public function testCopyContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_COPY_CONTENT,
            ContentEvents::COPY_CONTENT
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(LocationCreateStruct::class),
            $this->createMock(VersionInfo::class),
        ];

        $content = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('copyContent')->willReturn($content);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($content, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_COPY_CONTENT, 0],
            [ContentEvents::COPY_CONTENT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCopyContentResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_COPY_CONTENT,
            ContentEvents::COPY_CONTENT
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(LocationCreateStruct::class),
            $this->createMock(VersionInfo::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('copyContent')->willReturn($content);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_COPY_CONTENT, function (BeforeCopyContentEvent $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_COPY_CONTENT, 10],
            [ContentEvents::BEFORE_COPY_CONTENT, 0],
            [ContentEvents::COPY_CONTENT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCopyContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_COPY_CONTENT,
            ContentEvents::COPY_CONTENT
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(LocationCreateStruct::class),
            $this->createMock(VersionInfo::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('copyContent')->willReturn($content);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_COPY_CONTENT, function (BeforeCopyContentEvent $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_COPY_CONTENT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentEvents::COPY_CONTENT, 0],
            [ContentEvents::BEFORE_COPY_CONTENT, 0],
        ]);
    }

    public function testUpdateContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_UPDATE_CONTENT,
            ContentEvents::UPDATE_CONTENT
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContent')->willReturn($content);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($content, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_UPDATE_CONTENT, 0],
            [ContentEvents::UPDATE_CONTENT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateContentResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_UPDATE_CONTENT,
            ContentEvents::UPDATE_CONTENT
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContent')->willReturn($content);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_UPDATE_CONTENT, function (BeforeUpdateContentEvent $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_UPDATE_CONTENT, 10],
            [ContentEvents::BEFORE_UPDATE_CONTENT, 0],
            [ContentEvents::UPDATE_CONTENT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_UPDATE_CONTENT,
            ContentEvents::UPDATE_CONTENT
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContent')->willReturn($content);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_UPDATE_CONTENT, function (BeforeUpdateContentEvent $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_UPDATE_CONTENT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentEvents::UPDATE_CONTENT, 0],
            [ContentEvents::BEFORE_UPDATE_CONTENT, 0],
        ]);
    }

    public function testDeleteRelationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_DELETE_RELATION,
            ContentEvents::DELETE_RELATION
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteRelation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_DELETE_RELATION, 0],
            [ContentEvents::DELETE_RELATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteRelationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_DELETE_RELATION,
            ContentEvents::DELETE_RELATION
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_DELETE_RELATION, function (BeforeDeleteRelationEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteRelation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_DELETE_RELATION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentEvents::DELETE_RELATION, 0],
            [ContentEvents::BEFORE_DELETE_RELATION, 0],
        ]);
    }

    public function testCreateContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_CREATE_CONTENT,
            ContentEvents::CREATE_CONTENT
        );

        $parameters = [
            $this->createMock(ContentCreateStruct::class),
            [],
        ];

        $content = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('createContent')->willReturn($content);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($content, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_CREATE_CONTENT, 0],
            [ContentEvents::CREATE_CONTENT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_CREATE_CONTENT,
            ContentEvents::CREATE_CONTENT
        );

        $parameters = [
            $this->createMock(ContentCreateStruct::class),
            [],
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('createContent')->willReturn($content);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_CREATE_CONTENT, function (BeforeCreateContentEvent $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_CREATE_CONTENT, 10],
            [ContentEvents::BEFORE_CREATE_CONTENT, 0],
            [ContentEvents::CREATE_CONTENT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_CREATE_CONTENT,
            ContentEvents::CREATE_CONTENT
        );

        $parameters = [
            $this->createMock(ContentCreateStruct::class),
            [],
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('createContent')->willReturn($content);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_CREATE_CONTENT, function (BeforeCreateContentEvent $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_CREATE_CONTENT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentEvents::CREATE_CONTENT, 0],
            [ContentEvents::BEFORE_CREATE_CONTENT, 0],
        ]);
    }

    public function testHideContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_HIDE_CONTENT,
            ContentEvents::HIDE_CONTENT
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->hideContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_HIDE_CONTENT, 0],
            [ContentEvents::HIDE_CONTENT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testHideContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_HIDE_CONTENT,
            ContentEvents::HIDE_CONTENT
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_HIDE_CONTENT, function (BeforeHideContentEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->hideContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_HIDE_CONTENT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentEvents::HIDE_CONTENT, 0],
            [ContentEvents::BEFORE_HIDE_CONTENT, 0],
        ]);
    }

    public function testDeleteVersionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_DELETE_VERSION,
            ContentEvents::DELETE_VERSION
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_DELETE_VERSION, 0],
            [ContentEvents::DELETE_VERSION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteVersionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_DELETE_VERSION,
            ContentEvents::DELETE_VERSION
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_DELETE_VERSION, function (BeforeDeleteVersionEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_DELETE_VERSION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentEvents::DELETE_VERSION, 0],
            [ContentEvents::BEFORE_DELETE_VERSION, 0],
        ]);
    }

    public function testAddRelationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_ADD_RELATION,
            ContentEvents::ADD_RELATION
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentInfo::class),
        ];

        $relation = $this->createMock(Relation::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('addRelation')->willReturn($relation);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addRelation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($relation, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_ADD_RELATION, 0],
            [ContentEvents::ADD_RELATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnAddRelationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_ADD_RELATION,
            ContentEvents::ADD_RELATION
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentInfo::class),
        ];

        $relation = $this->createMock(Relation::class);
        $eventRelation = $this->createMock(Relation::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('addRelation')->willReturn($relation);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_ADD_RELATION, function (BeforeAddRelationEvent $event) use ($eventRelation) {
            $event->setRelation($eventRelation);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addRelation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventRelation, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_ADD_RELATION, 10],
            [ContentEvents::BEFORE_ADD_RELATION, 0],
            [ContentEvents::ADD_RELATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAddRelationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_ADD_RELATION,
            ContentEvents::ADD_RELATION
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentInfo::class),
        ];

        $relation = $this->createMock(Relation::class);
        $eventRelation = $this->createMock(Relation::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('addRelation')->willReturn($relation);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_ADD_RELATION, function (BeforeAddRelationEvent $event) use ($eventRelation) {
            $event->setRelation($eventRelation);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addRelation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventRelation, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_ADD_RELATION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentEvents::ADD_RELATION, 0],
            [ContentEvents::BEFORE_ADD_RELATION, 0],
        ]);
    }

    public function testUpdateContentMetadataEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_UPDATE_CONTENT_METADATA,
            ContentEvents::UPDATE_CONTENT_METADATA
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ContentMetadataUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContentMetadata')->willReturn($content);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContentMetadata(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($content, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_UPDATE_CONTENT_METADATA, 0],
            [ContentEvents::UPDATE_CONTENT_METADATA, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateContentMetadataResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_UPDATE_CONTENT_METADATA,
            ContentEvents::UPDATE_CONTENT_METADATA
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ContentMetadataUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContentMetadata')->willReturn($content);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_UPDATE_CONTENT_METADATA, function (BeforeUpdateContentMetadataEvent $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContentMetadata(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_UPDATE_CONTENT_METADATA, 10],
            [ContentEvents::BEFORE_UPDATE_CONTENT_METADATA, 0],
            [ContentEvents::UPDATE_CONTENT_METADATA, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateContentMetadataStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_UPDATE_CONTENT_METADATA,
            ContentEvents::UPDATE_CONTENT_METADATA
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ContentMetadataUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContentMetadata')->willReturn($content);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_UPDATE_CONTENT_METADATA, function (BeforeUpdateContentMetadataEvent $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContentMetadata(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_UPDATE_CONTENT_METADATA, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentEvents::UPDATE_CONTENT_METADATA, 0],
            [ContentEvents::BEFORE_UPDATE_CONTENT_METADATA, 0],
        ]);
    }

    public function testDeleteTranslationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_DELETE_TRANSLATION,
            ContentEvents::DELETE_TRANSLATION
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            'random_value_5cff79c31a2f31.74205767',
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteTranslation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_DELETE_TRANSLATION, 0],
            [ContentEvents::DELETE_TRANSLATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteTranslationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_DELETE_TRANSLATION,
            ContentEvents::DELETE_TRANSLATION
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            'random_value_5cff79c31a2fc0.71971617',
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_DELETE_TRANSLATION, function (BeforeDeleteTranslationEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteTranslation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_DELETE_TRANSLATION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentEvents::DELETE_TRANSLATION, 0],
            [ContentEvents::BEFORE_DELETE_TRANSLATION, 0],
        ]);
    }

    public function testPublishVersionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_PUBLISH_VERSION,
            ContentEvents::PUBLISH_VERSION
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
        ];

        $content = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('publishVersion')->willReturn($content);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->publishVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($content, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_PUBLISH_VERSION, 0],
            [ContentEvents::PUBLISH_VERSION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnPublishVersionResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_PUBLISH_VERSION,
            ContentEvents::PUBLISH_VERSION
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('publishVersion')->willReturn($content);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_PUBLISH_VERSION, function (BeforePublishVersionEvent $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->publishVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_PUBLISH_VERSION, 10],
            [ContentEvents::BEFORE_PUBLISH_VERSION, 0],
            [ContentEvents::PUBLISH_VERSION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testPublishVersionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_PUBLISH_VERSION,
            ContentEvents::PUBLISH_VERSION
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('publishVersion')->willReturn($content);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_PUBLISH_VERSION, function (BeforePublishVersionEvent $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->publishVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_PUBLISH_VERSION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentEvents::PUBLISH_VERSION, 0],
            [ContentEvents::BEFORE_PUBLISH_VERSION, 0],
        ]);
    }

    public function testCreateContentDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_CREATE_CONTENT_DRAFT,
            ContentEvents::CREATE_CONTENT_DRAFT
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(VersionInfo::class),
            $this->createMock(User::class),
        ];

        $contentDraft = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('createContentDraft')->willReturn($contentDraft);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($contentDraft, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_CREATE_CONTENT_DRAFT, 0],
            [ContentEvents::CREATE_CONTENT_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_CREATE_CONTENT_DRAFT,
            ContentEvents::CREATE_CONTENT_DRAFT
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(VersionInfo::class),
            $this->createMock(User::class),
        ];

        $contentDraft = $this->createMock(Content::class);
        $eventContentDraft = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('createContentDraft')->willReturn($contentDraft);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_CREATE_CONTENT_DRAFT, function (BeforeCreateContentDraftEvent $event) use ($eventContentDraft) {
            $event->setContentDraft($eventContentDraft);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentDraft, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_CREATE_CONTENT_DRAFT, 10],
            [ContentEvents::BEFORE_CREATE_CONTENT_DRAFT, 0],
            [ContentEvents::CREATE_CONTENT_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_CREATE_CONTENT_DRAFT,
            ContentEvents::CREATE_CONTENT_DRAFT
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(VersionInfo::class),
            $this->createMock(User::class),
        ];

        $contentDraft = $this->createMock(Content::class);
        $eventContentDraft = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('createContentDraft')->willReturn($contentDraft);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_CREATE_CONTENT_DRAFT, function (BeforeCreateContentDraftEvent $event) use ($eventContentDraft) {
            $event->setContentDraft($eventContentDraft);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentDraft, $result);
        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_CREATE_CONTENT_DRAFT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentEvents::CREATE_CONTENT_DRAFT, 0],
            [ContentEvents::BEFORE_CREATE_CONTENT_DRAFT, 0],
        ]);
    }

    public function testRevealContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_REVEAL_CONTENT,
            ContentEvents::REVEAL_CONTENT
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->revealContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_REVEAL_CONTENT, 0],
            [ContentEvents::REVEAL_CONTENT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRevealContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ContentEvents::BEFORE_REVEAL_CONTENT,
            ContentEvents::REVEAL_CONTENT
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(ContentEvents::BEFORE_REVEAL_CONTENT, function (BeforeRevealContentEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->revealContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ContentEvents::BEFORE_REVEAL_CONTENT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ContentEvents::REVEAL_CONTENT, 0],
            [ContentEvents::BEFORE_REVEAL_CONTENT, 0],
        ]);
    }
}
