<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Events\Tests;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Events\Content\AddRelationEvent as AddRelationEventInterface;
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
use eZ\Publish\API\Repository\Events\Content\CopyContentEvent as CopyContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\CreateContentDraftEvent as CreateContentDraftEventInterface;
use eZ\Publish\API\Repository\Events\Content\CreateContentEvent as CreateContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\DeleteContentEvent as DeleteContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\DeleteRelationEvent as DeleteRelationEventInterface;
use eZ\Publish\API\Repository\Events\Content\DeleteTranslationEvent as DeleteTranslationEventInterface;
use eZ\Publish\API\Repository\Events\Content\DeleteVersionEvent as DeleteVersionEventInterface;
use eZ\Publish\API\Repository\Events\Content\HideContentEvent as HideContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\PublishVersionEvent as PublishVersionEventInterface;
use eZ\Publish\API\Repository\Events\Content\RevealContentEvent as RevealContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\UpdateContentEvent as UpdateContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\UpdateContentMetadataEvent as UpdateContentMetadataEventInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Events\ContentService;

class ContentServiceTest extends AbstractServiceTest
{
    public function testDeleteContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentEventInterface::class,
            DeleteContentEventInterface::class
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
            [BeforeDeleteContentEventInterface::class, 0],
            [DeleteContentEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnDeleteContentResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentEventInterface::class,
            DeleteContentEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('deleteContent')->willReturn($locations);

        $traceableEventDispatcher->addListener(BeforeDeleteContentEventInterface::class, function (BeforeDeleteContentEventInterface $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteContentEventInterface::class, 10],
            [BeforeDeleteContentEventInterface::class, 0],
            [DeleteContentEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteContentEventInterface::class,
            DeleteContentEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('deleteContent')->willReturn($locations);

        $traceableEventDispatcher->addListener(BeforeDeleteContentEventInterface::class, function (BeforeDeleteContentEventInterface $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteContentEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteContentEventInterface::class, 0],
            [DeleteContentEventInterface::class, 0],
        ]);
    }

    public function testCopyContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopyContentEventInterface::class,
            CopyContentEventInterface::class
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
            [BeforeCopyContentEventInterface::class, 0],
            [CopyContentEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCopyContentResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopyContentEventInterface::class,
            CopyContentEventInterface::class
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

        $traceableEventDispatcher->addListener(BeforeCopyContentEventInterface::class, function (BeforeCopyContentEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeCopyContentEventInterface::class, 10],
            [BeforeCopyContentEventInterface::class, 0],
            [CopyContentEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCopyContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopyContentEventInterface::class,
            CopyContentEventInterface::class
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

        $traceableEventDispatcher->addListener(BeforeCopyContentEventInterface::class, function (BeforeCopyContentEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copyContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeCopyContentEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCopyContentEventInterface::class, 0],
            [CopyContentEventInterface::class, 0],
        ]);
    }

    public function testUpdateContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentEventInterface::class,
            UpdateContentEventInterface::class
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
            [BeforeUpdateContentEventInterface::class, 0],
            [UpdateContentEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateContentResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentEventInterface::class,
            UpdateContentEventInterface::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContent')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforeUpdateContentEventInterface::class, function (BeforeUpdateContentEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateContentEventInterface::class, 10],
            [BeforeUpdateContentEventInterface::class, 0],
            [UpdateContentEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentEventInterface::class,
            UpdateContentEventInterface::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContent')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforeUpdateContentEventInterface::class, function (BeforeUpdateContentEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateContentEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateContentEventInterface::class, 0],
            [UpdateContentEventInterface::class, 0],
        ]);
    }

    public function testDeleteRelationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteRelationEventInterface::class,
            DeleteRelationEventInterface::class
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
            [BeforeDeleteRelationEventInterface::class, 0],
            [DeleteRelationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteRelationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteRelationEventInterface::class,
            DeleteRelationEventInterface::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteRelationEventInterface::class, function (BeforeDeleteRelationEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteRelation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteRelationEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteRelationEventInterface::class, 0],
            [DeleteRelationEventInterface::class, 0],
        ]);
    }

    public function testCreateContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentEventInterface::class,
            CreateContentEventInterface::class
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
            [BeforeCreateContentEventInterface::class, 0],
            [CreateContentEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentEventInterface::class,
            CreateContentEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentCreateStruct::class),
            [],
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('createContent')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforeCreateContentEventInterface::class, function (BeforeCreateContentEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentEventInterface::class, 10],
            [BeforeCreateContentEventInterface::class, 0],
            [CreateContentEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentEventInterface::class,
            CreateContentEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentCreateStruct::class),
            [],
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('createContent')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforeCreateContentEventInterface::class, function (BeforeCreateContentEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateContentEventInterface::class, 0],
            [CreateContentEventInterface::class, 0],
        ]);
    }

    public function testHideContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeHideContentEventInterface::class,
            HideContentEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->hideContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeHideContentEventInterface::class, 0],
            [HideContentEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testHideContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeHideContentEventInterface::class,
            HideContentEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeHideContentEventInterface::class, function (BeforeHideContentEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->hideContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeHideContentEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeHideContentEventInterface::class, 0],
            [HideContentEventInterface::class, 0],
        ]);
    }

    public function testDeleteVersionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteVersionEventInterface::class,
            DeleteVersionEventInterface::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteVersionEventInterface::class, 0],
            [DeleteVersionEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteVersionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteVersionEventInterface::class,
            DeleteVersionEventInterface::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteVersionEventInterface::class, function (BeforeDeleteVersionEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteVersionEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteVersionEventInterface::class, 0],
            [DeleteVersionEventInterface::class, 0],
        ]);
    }

    public function testAddRelationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddRelationEventInterface::class,
            AddRelationEventInterface::class
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
            [BeforeAddRelationEventInterface::class, 0],
            [AddRelationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnAddRelationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddRelationEventInterface::class,
            AddRelationEventInterface::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentInfo::class),
        ];

        $relation = $this->createMock(Relation::class);
        $eventRelation = $this->createMock(Relation::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('addRelation')->willReturn($relation);

        $traceableEventDispatcher->addListener(BeforeAddRelationEventInterface::class, function (BeforeAddRelationEventInterface $event) use ($eventRelation) {
            $event->setRelation($eventRelation);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addRelation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventRelation, $result);
        $this->assertSame($calledListeners, [
            [BeforeAddRelationEventInterface::class, 10],
            [BeforeAddRelationEventInterface::class, 0],
            [AddRelationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAddRelationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddRelationEventInterface::class,
            AddRelationEventInterface::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            $this->createMock(ContentInfo::class),
        ];

        $relation = $this->createMock(Relation::class);
        $eventRelation = $this->createMock(Relation::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('addRelation')->willReturn($relation);

        $traceableEventDispatcher->addListener(BeforeAddRelationEventInterface::class, function (BeforeAddRelationEventInterface $event) use ($eventRelation) {
            $event->setRelation($eventRelation);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addRelation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventRelation, $result);
        $this->assertSame($calledListeners, [
            [BeforeAddRelationEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AddRelationEventInterface::class, 0],
            [BeforeAddRelationEventInterface::class, 0],
        ]);
    }

    public function testUpdateContentMetadataEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentMetadataEventInterface::class,
            UpdateContentMetadataEventInterface::class
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
            [BeforeUpdateContentMetadataEventInterface::class, 0],
            [UpdateContentMetadataEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateContentMetadataResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentMetadataEventInterface::class,
            UpdateContentMetadataEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ContentMetadataUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContentMetadata')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforeUpdateContentMetadataEventInterface::class, function (BeforeUpdateContentMetadataEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContentMetadata(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateContentMetadataEventInterface::class, 10],
            [BeforeUpdateContentMetadataEventInterface::class, 0],
            [UpdateContentMetadataEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateContentMetadataStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateContentMetadataEventInterface::class,
            UpdateContentMetadataEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ContentMetadataUpdateStruct::class),
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('updateContentMetadata')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforeUpdateContentMetadataEventInterface::class, function (BeforeUpdateContentMetadataEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateContentMetadata(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateContentMetadataEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateContentMetadataEventInterface::class, 0],
            [UpdateContentMetadataEventInterface::class, 0],
        ]);
    }

    public function testDeleteTranslationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteTranslationEventInterface::class,
            DeleteTranslationEventInterface::class
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
            [BeforeDeleteTranslationEventInterface::class, 0],
            [DeleteTranslationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteTranslationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteTranslationEventInterface::class,
            DeleteTranslationEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            'random_value_5cff79c31a2fc0.71971617',
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteTranslationEventInterface::class, function (BeforeDeleteTranslationEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteTranslation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteTranslationEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteTranslationEventInterface::class, 0],
            [DeleteTranslationEventInterface::class, 0],
        ]);
    }

    public function testPublishVersionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforePublishVersionEventInterface::class,
            PublishVersionEventInterface::class
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
            [BeforePublishVersionEventInterface::class, 0],
            [PublishVersionEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnPublishVersionResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforePublishVersionEventInterface::class,
            PublishVersionEventInterface::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            [],
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('publishVersion')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforePublishVersionEventInterface::class, function (BeforePublishVersionEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->publishVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforePublishVersionEventInterface::class, 10],
            [BeforePublishVersionEventInterface::class, 0],
            [PublishVersionEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testPublishVersionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforePublishVersionEventInterface::class,
            PublishVersionEventInterface::class
        );

        $parameters = [
            $this->createMock(VersionInfo::class),
            [],
        ];

        $content = $this->createMock(Content::class);
        $eventContent = $this->createMock(Content::class);
        $innerServiceMock = $this->createMock(ContentServiceInterface::class);
        $innerServiceMock->method('publishVersion')->willReturn($content);

        $traceableEventDispatcher->addListener(BeforePublishVersionEventInterface::class, function (BeforePublishVersionEventInterface $event) use ($eventContent) {
            $event->setContent($eventContent);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->publishVersion(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContent, $result);
        $this->assertSame($calledListeners, [
            [BeforePublishVersionEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforePublishVersionEventInterface::class, 0],
            [PublishVersionEventInterface::class, 0],
        ]);
    }

    public function testCreateContentDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentDraftEventInterface::class,
            CreateContentDraftEventInterface::class
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
            [BeforeCreateContentDraftEventInterface::class, 0],
            [CreateContentDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateContentDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentDraftEventInterface::class,
            CreateContentDraftEventInterface::class
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

        $traceableEventDispatcher->addListener(BeforeCreateContentDraftEventInterface::class, function (BeforeCreateContentDraftEventInterface $event) use ($eventContentDraft) {
            $event->setContentDraft($eventContentDraft);
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventContentDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentDraftEventInterface::class, 10],
            [BeforeCreateContentDraftEventInterface::class, 0],
            [CreateContentDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateContentDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateContentDraftEventInterface::class,
            CreateContentDraftEventInterface::class
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

        $traceableEventDispatcher->addListener(BeforeCreateContentDraftEventInterface::class, function (BeforeCreateContentDraftEventInterface $event) use ($eventContentDraft) {
            $event->setContentDraft($eventContentDraft);
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createContentDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventContentDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateContentDraftEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateContentDraftEventInterface::class, 0],
            [CreateContentDraftEventInterface::class, 0],
        ]);
    }

    public function testRevealContentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRevealContentEventInterface::class,
            RevealContentEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->revealContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeRevealContentEventInterface::class, 0],
            [RevealContentEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRevealContentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRevealContentEventInterface::class,
            RevealContentEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
        ];

        $innerServiceMock = $this->createMock(ContentServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeRevealContentEventInterface::class, function (BeforeRevealContentEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ContentService($innerServiceMock, $traceableEventDispatcher);
        $service->revealContent(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeRevealContentEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeRevealContentEventInterface::class, 0],
            [RevealContentEventInterface::class, 0],
        ]);
    }
}
