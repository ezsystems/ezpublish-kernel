<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\SectionService as SectionServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\Core\Event\SectionService;
use eZ\Publish\Core\Event\Section\BeforeAssignSectionEvent;
use eZ\Publish\Core\Event\Section\BeforeAssignSectionToSubtreeEvent;
use eZ\Publish\Core\Event\Section\BeforeCreateSectionEvent;
use eZ\Publish\Core\Event\Section\BeforeDeleteSectionEvent;
use eZ\Publish\Core\Event\Section\BeforeUpdateSectionEvent;
use eZ\Publish\Core\Event\Section\SectionEvents;

class SectionServiceTest extends AbstractServiceTest
{
    public function testAssignSectionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            SectionEvents::BEFORE_ASSIGN_SECTION,
            SectionEvents::ASSIGN_SECTION
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->assignSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [SectionEvents::BEFORE_ASSIGN_SECTION, 0],
            [SectionEvents::ASSIGN_SECTION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignSectionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            SectionEvents::BEFORE_ASSIGN_SECTION,
            SectionEvents::ASSIGN_SECTION
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $traceableEventDispatcher->addListener(SectionEvents::BEFORE_ASSIGN_SECTION, function (BeforeAssignSectionEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->assignSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [SectionEvents::BEFORE_ASSIGN_SECTION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [SectionEvents::ASSIGN_SECTION, 0],
            [SectionEvents::BEFORE_ASSIGN_SECTION, 0],
        ]);
    }

    public function testUpdateSectionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            SectionEvents::BEFORE_UPDATE_SECTION,
            SectionEvents::UPDATE_SECTION
        );

        $parameters = [
            $this->createMock(Section::class),
            $this->createMock(SectionUpdateStruct::class),
        ];

        $updatedSection = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('updateSection')->willReturn($updatedSection);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedSection, $result);
        $this->assertSame($calledListeners, [
            [SectionEvents::BEFORE_UPDATE_SECTION, 0],
            [SectionEvents::UPDATE_SECTION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateSectionResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            SectionEvents::BEFORE_UPDATE_SECTION,
            SectionEvents::UPDATE_SECTION
        );

        $parameters = [
            $this->createMock(Section::class),
            $this->createMock(SectionUpdateStruct::class),
        ];

        $updatedSection = $this->createMock(Section::class);
        $eventUpdatedSection = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('updateSection')->willReturn($updatedSection);

        $traceableEventDispatcher->addListener(SectionEvents::BEFORE_UPDATE_SECTION, function (BeforeUpdateSectionEvent $event) use ($eventUpdatedSection) {
            $event->setUpdatedSection($eventUpdatedSection);
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedSection, $result);
        $this->assertSame($calledListeners, [
            [SectionEvents::BEFORE_UPDATE_SECTION, 10],
            [SectionEvents::BEFORE_UPDATE_SECTION, 0],
            [SectionEvents::UPDATE_SECTION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateSectionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            SectionEvents::BEFORE_UPDATE_SECTION,
            SectionEvents::UPDATE_SECTION
        );

        $parameters = [
            $this->createMock(Section::class),
            $this->createMock(SectionUpdateStruct::class),
        ];

        $updatedSection = $this->createMock(Section::class);
        $eventUpdatedSection = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('updateSection')->willReturn($updatedSection);

        $traceableEventDispatcher->addListener(SectionEvents::BEFORE_UPDATE_SECTION, function (BeforeUpdateSectionEvent $event) use ($eventUpdatedSection) {
            $event->setUpdatedSection($eventUpdatedSection);
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedSection, $result);
        $this->assertSame($calledListeners, [
            [SectionEvents::BEFORE_UPDATE_SECTION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [SectionEvents::UPDATE_SECTION, 0],
            [SectionEvents::BEFORE_UPDATE_SECTION, 0],
        ]);
    }

    public function testAssignSectionToSubtreeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            SectionEvents::BEFORE_ASSIGN_SECTION_TO_SUBTREE,
            SectionEvents::ASSIGN_SECTION_TO_SUBTREE
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->assignSectionToSubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [SectionEvents::BEFORE_ASSIGN_SECTION_TO_SUBTREE, 0],
            [SectionEvents::ASSIGN_SECTION_TO_SUBTREE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignSectionToSubtreeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            SectionEvents::BEFORE_ASSIGN_SECTION_TO_SUBTREE,
            SectionEvents::ASSIGN_SECTION_TO_SUBTREE
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $traceableEventDispatcher->addListener(SectionEvents::BEFORE_ASSIGN_SECTION_TO_SUBTREE, function (BeforeAssignSectionToSubtreeEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->assignSectionToSubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [SectionEvents::BEFORE_ASSIGN_SECTION_TO_SUBTREE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [SectionEvents::ASSIGN_SECTION_TO_SUBTREE, 0],
            [SectionEvents::BEFORE_ASSIGN_SECTION_TO_SUBTREE, 0],
        ]);
    }

    public function testDeleteSectionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            SectionEvents::BEFORE_DELETE_SECTION,
            SectionEvents::DELETE_SECTION
        );

        $parameters = [
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [SectionEvents::BEFORE_DELETE_SECTION, 0],
            [SectionEvents::DELETE_SECTION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteSectionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            SectionEvents::BEFORE_DELETE_SECTION,
            SectionEvents::DELETE_SECTION
        );

        $parameters = [
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $traceableEventDispatcher->addListener(SectionEvents::BEFORE_DELETE_SECTION, function (BeforeDeleteSectionEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [SectionEvents::BEFORE_DELETE_SECTION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [SectionEvents::DELETE_SECTION, 0],
            [SectionEvents::BEFORE_DELETE_SECTION, 0],
        ]);
    }

    public function testCreateSectionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            SectionEvents::BEFORE_CREATE_SECTION,
            SectionEvents::CREATE_SECTION
        );

        $parameters = [
            $this->createMock(SectionCreateStruct::class),
        ];

        $section = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('createSection')->willReturn($section);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($section, $result);
        $this->assertSame($calledListeners, [
            [SectionEvents::BEFORE_CREATE_SECTION, 0],
            [SectionEvents::CREATE_SECTION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateSectionResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            SectionEvents::BEFORE_CREATE_SECTION,
            SectionEvents::CREATE_SECTION
        );

        $parameters = [
            $this->createMock(SectionCreateStruct::class),
        ];

        $section = $this->createMock(Section::class);
        $eventSection = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('createSection')->willReturn($section);

        $traceableEventDispatcher->addListener(SectionEvents::BEFORE_CREATE_SECTION, function (BeforeCreateSectionEvent $event) use ($eventSection) {
            $event->setSection($eventSection);
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventSection, $result);
        $this->assertSame($calledListeners, [
            [SectionEvents::BEFORE_CREATE_SECTION, 10],
            [SectionEvents::BEFORE_CREATE_SECTION, 0],
            [SectionEvents::CREATE_SECTION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateSectionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            SectionEvents::BEFORE_CREATE_SECTION,
            SectionEvents::CREATE_SECTION
        );

        $parameters = [
            $this->createMock(SectionCreateStruct::class),
        ];

        $section = $this->createMock(Section::class);
        $eventSection = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('createSection')->willReturn($section);

        $traceableEventDispatcher->addListener(SectionEvents::BEFORE_CREATE_SECTION, function (BeforeCreateSectionEvent $event) use ($eventSection) {
            $event->setSection($eventSection);
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventSection, $result);
        $this->assertSame($calledListeners, [
            [SectionEvents::BEFORE_CREATE_SECTION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [SectionEvents::CREATE_SECTION, 0],
            [SectionEvents::BEFORE_CREATE_SECTION, 0],
        ]);
    }
}
