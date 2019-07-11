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
use eZ\Publish\Core\Event\Section\AssignSectionEvent;
use eZ\Publish\Core\Event\Section\AssignSectionToSubtreeEvent;
use eZ\Publish\Core\Event\Section\BeforeAssignSectionEvent;
use eZ\Publish\Core\Event\Section\BeforeAssignSectionToSubtreeEvent;
use eZ\Publish\Core\Event\Section\BeforeCreateSectionEvent;
use eZ\Publish\Core\Event\Section\BeforeDeleteSectionEvent;
use eZ\Publish\Core\Event\Section\BeforeUpdateSectionEvent;
use eZ\Publish\Core\Event\Section\CreateSectionEvent;
use eZ\Publish\Core\Event\Section\DeleteSectionEvent;
use eZ\Publish\Core\Event\Section\UpdateSectionEvent;
use eZ\Publish\Core\Event\SectionService;

class SectionServiceTest extends AbstractServiceTest
{
    public function testAssignSectionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignSectionEvent::class,
            AssignSectionEvent::class
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
            [BeforeAssignSectionEvent::class, 0],
            [AssignSectionEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignSectionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignSectionEvent::class,
            AssignSectionEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeAssignSectionEvent::class, function (\eZ\Publish\API\Repository\Events\Section\BeforeAssignSectionEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->assignSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAssignSectionEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AssignSectionEvent::class, 0],
            [BeforeAssignSectionEvent::class, 0],
        ]);
    }

    public function testUpdateSectionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateSectionEvent::class,
            UpdateSectionEvent::class
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
            [BeforeUpdateSectionEvent::class, 0],
            [UpdateSectionEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateSectionResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateSectionEvent::class,
            UpdateSectionEvent::class
        );

        $parameters = [
            $this->createMock(Section::class),
            $this->createMock(SectionUpdateStruct::class),
        ];

        $updatedSection = $this->createMock(Section::class);
        $eventUpdatedSection = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('updateSection')->willReturn($updatedSection);

        $traceableEventDispatcher->addListener(BeforeUpdateSectionEvent::class, function (\eZ\Publish\API\Repository\Events\Section\BeforeUpdateSectionEvent $event) use ($eventUpdatedSection) {
            $event->setUpdatedSection($eventUpdatedSection);
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedSection, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateSectionEvent::class, 10],
            [BeforeUpdateSectionEvent::class, 0],
            [UpdateSectionEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateSectionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateSectionEvent::class,
            UpdateSectionEvent::class
        );

        $parameters = [
            $this->createMock(Section::class),
            $this->createMock(SectionUpdateStruct::class),
        ];

        $updatedSection = $this->createMock(Section::class);
        $eventUpdatedSection = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('updateSection')->willReturn($updatedSection);

        $traceableEventDispatcher->addListener(BeforeUpdateSectionEvent::class, function (\eZ\Publish\API\Repository\Events\Section\BeforeUpdateSectionEvent $event) use ($eventUpdatedSection) {
            $event->setUpdatedSection($eventUpdatedSection);
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedSection, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateSectionEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateSectionEvent::class, 0],
            [UpdateSectionEvent::class, 0],
        ]);
    }

    public function testAssignSectionToSubtreeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignSectionToSubtreeEvent::class,
            AssignSectionToSubtreeEvent::class
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
            [BeforeAssignSectionToSubtreeEvent::class, 0],
            [AssignSectionToSubtreeEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignSectionToSubtreeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignSectionToSubtreeEvent::class,
            AssignSectionToSubtreeEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeAssignSectionToSubtreeEvent::class, function (\eZ\Publish\API\Repository\Events\Section\BeforeAssignSectionToSubtreeEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->assignSectionToSubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAssignSectionToSubtreeEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AssignSectionToSubtreeEvent::class, 0],
            [BeforeAssignSectionToSubtreeEvent::class, 0],
        ]);
    }

    public function testDeleteSectionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteSectionEvent::class,
            DeleteSectionEvent::class
        );

        $parameters = [
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteSectionEvent::class, 0],
            [DeleteSectionEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteSectionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteSectionEvent::class,
            DeleteSectionEvent::class
        );

        $parameters = [
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteSectionEvent::class, function (\eZ\Publish\API\Repository\Events\Section\BeforeDeleteSectionEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteSectionEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteSectionEvent::class, 0],
            [DeleteSectionEvent::class, 0],
        ]);
    }

    public function testCreateSectionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateSectionEvent::class,
            CreateSectionEvent::class
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
            [BeforeCreateSectionEvent::class, 0],
            [CreateSectionEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateSectionResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateSectionEvent::class,
            CreateSectionEvent::class
        );

        $parameters = [
            $this->createMock(SectionCreateStruct::class),
        ];

        $section = $this->createMock(Section::class);
        $eventSection = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('createSection')->willReturn($section);

        $traceableEventDispatcher->addListener(BeforeCreateSectionEvent::class, function (\eZ\Publish\API\Repository\Events\Section\BeforeCreateSectionEvent $event) use ($eventSection) {
            $event->setSection($eventSection);
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventSection, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateSectionEvent::class, 10],
            [BeforeCreateSectionEvent::class, 0],
            [CreateSectionEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateSectionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateSectionEvent::class,
            CreateSectionEvent::class
        );

        $parameters = [
            $this->createMock(SectionCreateStruct::class),
        ];

        $section = $this->createMock(Section::class);
        $eventSection = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('createSection')->willReturn($section);

        $traceableEventDispatcher->addListener(BeforeCreateSectionEvent::class, function (\eZ\Publish\API\Repository\Events\Section\BeforeCreateSectionEvent $event) use ($eventSection) {
            $event->setSection($eventSection);
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventSection, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateSectionEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateSectionEvent::class, 0],
            [CreateSectionEvent::class, 0],
        ]);
    }
}
