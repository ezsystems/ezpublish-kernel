<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeAddRelationEvent as BeforeAddRelationEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeCopyContentEvent as BeforeCopyContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeCreateContentDraftEvent as BeforeCreateContentDraftEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeCreateContentEvent as BeforeCreateContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeDeleteContentEvent as BeforeDeleteContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeDeleteRelationEvent as BeforeDeleteRelationEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeDeleteTranslationEvent as BeforeDeleteTranslationEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeDeleteVersionEvent as BeforeDeleteVersionEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeHideContentEvent as BeforeHideContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforePublishVersionEvent as BeforePublishVersionEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeRevealContentEvent as BeforeRevealContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeUpdateContentEvent as BeforeUpdateContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeUpdateContentMetadataEvent as BeforeUpdateContentMetadataEventInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Event\Content\AddRelationEvent;
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
use eZ\Publish\Core\Event\Content\CopyContentEvent;
use eZ\Publish\Core\Event\Content\CreateContentDraftEvent;
use eZ\Publish\Core\Event\Content\CreateContentEvent;
use eZ\Publish\Core\Event\Content\DeleteContentEvent;
use eZ\Publish\Core\Event\Content\DeleteRelationEvent;
use eZ\Publish\Core\Event\Content\DeleteTranslationEvent;
use eZ\Publish\Core\Event\Content\DeleteVersionEvent;
use eZ\Publish\Core\Event\Content\HideContentEvent;
use eZ\Publish\Core\Event\Content\PublishVersionEvent;
use eZ\Publish\Core\Event\Content\RevealContentEvent;
use eZ\Publish\Core\Event\Content\UpdateContentEvent;
use eZ\Publish\Core\Event\Content\UpdateContentMetadataEvent;
use eZ\Publish\Core\Event\ContentService;

class ContentServiceTest extends AbstractServiceTest
{
    public function testDeleteContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentEvent::class,
            DeleteContentEvent::class
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
            [BeforeDeleteContentEvent::class, 0],
            [DeleteContentEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnDeleteContentResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentEvent::class,
            DeleteContentEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('deleteContent')->willReturn($locations);

        $traceableEventDispatcher->addListener(BeforeDeleteContentEvent::class, function (BeforeDeleteContentEventInterface $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteContentEvent::class, 10],
            [BeforeDeleteContentEvent::class, 0],
            [DeleteContentEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentEvent::class,
            DeleteContentEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('deleteContent')->willReturn($locations);

        $traceableEventDispatcher->addListener(BeforeDeleteContentEvent::class, function (BeforeDeleteContentEventInterface $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteContentEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteContentEvent::class, 0],
            [DeleteContentEvent::class, 0],
        ]);
    }

    public function testCopyContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopyContentEvent::class,
            CopyContentEvent::class
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
            [BeforeCopyContentEvent::class, 0],
            [CopyContentEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCopyContentResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopyContentEvent::class,
            CopyContentEvent::class
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

        $traceableEventDispatcher->addListener(BeforeCopyContentEvent::class, function (BeforeCopyContentEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeCopyContentEvent::class, 10],
            [BeforeCopyContentEvent::class, 0],
            [CopyContentEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCopyContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopyContentEvent::class,
            CopyContentEvent::class
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

        $traceableEventDispatcher->addListener(BeforeCopyContentEvent::class, function (BeforeCopyContentEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeCopyContentEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCopyContentEvent::class, 0],
            [CopyContentEvent::class, 0],
        ]);
    }

    public function testUpdateContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentEvent::class,
            UpdateContentEvent::class
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
            [BeforeUpdateContentEvent::class, 0],
            [UpdateContentEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateContentResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentEvent::class,
            UpdateContentEvent::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContent')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforeUpdateContentEvent::class, function (BeforeUpdateContentEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateContentEvent::class, 10],
            [BeforeUpdateContentEvent::class, 0],
            [UpdateContentEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentEvent::class,
            UpdateContentEvent::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContent')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforeUpdateContentEvent::class, function (BeforeUpdateContentEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateContentEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateContentEvent::class, 0],
            [UpdateContentEvent::class, 0],
        ]);
    }

    public function testDeleteRelationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteRelationEvent::class,
            DeleteRelationEvent::class
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
            [BeforeDeleteRelationEvent::class, 0],
            [DeleteRelationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteRelationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteRelationEvent::class,
            DeleteRelationEvent::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteRelationEvent::class, function (BeforeDeleteRelationEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteRelation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteRelationEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteRelationEvent::class, 0],
            [DeleteRelationEvent::class, 0],
        ]);
    }

    public function testCreateContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentEvent::class,
            CreateContentEvent::class
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
            [BeforeCreateContentEvent::class, 0],
            [CreateContentEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentEvent::class,
            CreateContentEvent::class
        );

        $parameters = [
            $this->createMock(ContentCreateStruct::class),
            [],
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('createContent')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforeCreateContentEvent::class, function (BeforeCreateContentEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentEvent::class, 10],
            [BeforeCreateContentEvent::class, 0],
            [CreateContentEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentEvent::class,
            CreateContentEvent::class
        );

        $parameters = [
            $this->createMock(ContentCreateStruct::class),
            [],
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('createContent')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforeCreateContentEvent::class, function (BeforeCreateContentEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateContentEvent::class, 0],
            [CreateContentEvent::class, 0],
        ]);
    }

    public function testHideContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeHideContentEvent::class,
            HideContentEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->hideContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeHideContentEvent::class, 0],
            [HideContentEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testHideContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeHideContentEvent::class,
            HideContentEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeHideContentEvent::class, function (BeforeHideContentEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->hideContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeHideContentEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeHideContentEvent::class, 0],
            [HideContentEvent::class, 0],
        ]);
    }

    public function testDeleteVersionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteVersionEvent::class,
            DeleteVersionEvent::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteVersionEvent::class, 0],
            [DeleteVersionEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteVersionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteVersionEvent::class,
            DeleteVersionEvent::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteVersionEvent::class, function (BeforeDeleteVersionEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteVersionEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteVersionEvent::class, 0],
            [DeleteVersionEvent::class, 0],
        ]);
    }

    public function testAddRelationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddRelationEvent::class,
            AddRelationEvent::class
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
            [BeforeAddRelationEvent::class, 0],
            [AddRelationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnAddRelationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddRelationEvent::class,
            AddRelationEvent::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentInfo::class),
        ];

        $relation = $this->createMock(Relation::class);
        $eventRelation = $this->createMock(Relation::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('addRelation')->willReturn($relation);

        $traceableEventDispatcher->addListener(BeforeAddRelationEvent::class, function (BeforeAddRelationEventInterface $event) use ($eventRelation) {
            $event->setRelation($eventRelation);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addRelation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventRelation, $result);
        $this->assertSame($calledListeners, [
            [BeforeAddRelationEvent::class, 10],
            [BeforeAddRelationEvent::class, 0],
            [AddRelationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAddRelationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddRelationEvent::class,
            AddRelationEvent::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentInfo::class),
        ];

        $relation = $this->createMock(Relation::class);
        $eventRelation = $this->createMock(Relation::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('addRelation')->willReturn($relation);

        $traceableEventDispatcher->addListener(BeforeAddRelationEvent::class, function (BeforeAddRelationEventInterface $event) use ($eventRelation) {
            $event->setRelation($eventRelation);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addRelation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventRelation, $result);
        $this->assertSame($calledListeners, [
            [BeforeAddRelationEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AddRelationEvent::class, 0],
            [BeforeAddRelationEvent::class, 0],
        ]);
    }

    public function testUpdateContentMetadataEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentMetadataEvent::class,
            UpdateContentMetadataEvent::class
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
            [BeforeUpdateContentMetadataEvent::class, 0],
            [UpdateContentMetadataEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateContentMetadataResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentMetadataEvent::class,
            UpdateContentMetadataEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ContentMetadataUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContentMetadata')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforeUpdateContentMetadataEvent::class, function (BeforeUpdateContentMetadataEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContentMetadata(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateContentMetadataEvent::class, 10],
            [BeforeUpdateContentMetadataEvent::class, 0],
            [UpdateContentMetadataEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateContentMetadataStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentMetadataEvent::class,
            UpdateContentMetadataEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ContentMetadataUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContentMetadata')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforeUpdateContentMetadataEvent::class, function (BeforeUpdateContentMetadataEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContentMetadata(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateContentMetadataEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateContentMetadataEvent::class, 0],
            [UpdateContentMetadataEvent::class, 0],
        ]);
    }

    public function testDeleteTranslationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteTranslationEvent::class,
            DeleteTranslationEvent::class
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
            [BeforeDeleteTranslationEvent::class, 0],
            [DeleteTranslationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteTranslationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteTranslationEvent::class,
            DeleteTranslationEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            'random_value_5cff79c31a2fc0.71971617',
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteTranslationEvent::class, function (BeforeDeleteTranslationEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteTranslation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteTranslationEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteTranslationEvent::class, 0],
            [DeleteTranslationEvent::class, 0],
        ]);
    }

    public function testPublishVersionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforePublishVersionEvent::class,
            PublishVersionEvent::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            [],
        ];

        $content = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('publishVersion')->willReturn($content);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->publishVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($content, $result);
        $this->assertSame($calledListeners, [
            [BeforePublishVersionEvent::class, 0],
            [PublishVersionEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnPublishVersionResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforePublishVersionEvent::class,
            PublishVersionEvent::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            [],
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('publishVersion')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforePublishVersionEvent::class, function (BeforePublishVersionEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->publishVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforePublishVersionEvent::class, 10],
            [BeforePublishVersionEvent::class, 0],
            [PublishVersionEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testPublishVersionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforePublishVersionEvent::class,
            PublishVersionEvent::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            [],
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('publishVersion')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforePublishVersionEvent::class, function (BeforePublishVersionEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->publishVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforePublishVersionEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforePublishVersionEvent::class, 0],
            [PublishVersionEvent::class, 0],
        ]);
    }

    public function testCreateContentDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentDraftEvent::class,
            CreateContentDraftEvent::class
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
            [BeforeCreateContentDraftEvent::class, 0],
            [CreateContentDraftEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentDraftEvent::class,
            CreateContentDraftEvent::class
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

        $traceableEventDispatcher->addListener(BeforeCreateContentDraftEvent::class, function (BeforeCreateContentDraftEventInterface $event) use ($eventContentDraft) {
            $event->setContentDraft($eventContentDraft);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentDraftEvent::class, 10],
            [BeforeCreateContentDraftEvent::class, 0],
            [CreateContentDraftEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentDraftEvent::class,
            CreateContentDraftEvent::class
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

        $traceableEventDispatcher->addListener(BeforeCreateContentDraftEvent::class, function (BeforeCreateContentDraftEventInterface $event) use ($eventContentDraft) {
            $event->setContentDraft($eventContentDraft);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentDraftEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateContentDraftEvent::class, 0],
            [CreateContentDraftEvent::class, 0],
        ]);
    }

    public function testRevealContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRevealContentEvent::class,
            RevealContentEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->revealContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeRevealContentEvent::class, 0],
            [RevealContentEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRevealContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRevealContentEvent::class,
            RevealContentEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeRevealContentEvent::class, function (BeforeRevealContentEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->revealContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeRevealContentEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeRevealContentEvent::class, 0],
            [RevealContentEvent::class, 0],
        ]);
    }
}
