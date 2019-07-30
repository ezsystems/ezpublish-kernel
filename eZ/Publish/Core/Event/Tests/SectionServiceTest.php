<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\Events\Section\AssignSectionEvent as AssignSectionEventInterface;
use eZ\Publish\API\Repository\Events\Section\AssignSectionToSubtreeEvent as AssignSectionToSubtreeEventInterface;
use eZ\Publish\API\Repository\Events\Section\BeforeAssignSectionEvent as BeforeAssignSectionEventInterface;
use eZ\Publish\API\Repository\Events\Section\BeforeAssignSectionToSubtreeEvent as BeforeAssignSectionToSubtreeEventInterface;
use eZ\Publish\API\Repository\Events\Section\BeforeCreateSectionEvent as BeforeCreateSectionEventInterface;
use eZ\Publish\API\Repository\Events\Section\BeforeDeleteSectionEvent as BeforeDeleteSectionEventInterface;
use eZ\Publish\API\Repository\Events\Section\BeforeUpdateSectionEvent as BeforeUpdateSectionEventInterface;
use eZ\Publish\API\Repository\Events\Section\CreateSectionEvent as CreateSectionEventInterface;
use eZ\Publish\API\Repository\Events\Section\DeleteSectionEvent as DeleteSectionEventInterface;
use eZ\Publish\API\Repository\Events\Section\UpdateSectionEvent as UpdateSectionEventInterface;
use eZ\Publish\API\Repository\SectionService as SectionServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\Core\Event\SectionService;

class SectionServiceTest extends AbstractServiceTest
{
    public function testAssignSectionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignSectionEventInterface::class,
            AssignSectionEventInterface::class
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
            [BeforeAssignSectionEventInterface::class, 0],
            [AssignSectionEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignSectionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignSectionEventInterface::class,
            AssignSectionEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeAssignSectionEventInterface::class, function (BeforeAssignSectionEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->assignSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAssignSectionEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AssignSectionEventInterface::class, 0],
            [BeforeAssignSectionEventInterface::class, 0],
        ]);
    }

    public function testUpdateSectionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateSectionEventInterface::class,
            UpdateSectionEventInterface::class
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
            [BeforeUpdateSectionEventInterface::class, 0],
            [UpdateSectionEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateSectionResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateSectionEventInterface::class,
            UpdateSectionEventInterface::class
        );

        $parameters = [
            $this->createMock(Section::class),
            $this->createMock(SectionUpdateStruct::class),
        ];

        $updatedSection = $this->createMock(Section::class);
        $eventUpdatedSection = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('updateSection')->willReturn($updatedSection);

        $traceableEventDispatcher->addListener(BeforeUpdateSectionEventInterface::class, function (BeforeUpdateSectionEventInterface $event) use ($eventUpdatedSection) {
            $event->setUpdatedSection($eventUpdatedSection);
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedSection, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateSectionEventInterface::class, 10],
            [BeforeUpdateSectionEventInterface::class, 0],
            [UpdateSectionEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateSectionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateSectionEventInterface::class,
            UpdateSectionEventInterface::class
        );

        $parameters = [
            $this->createMock(Section::class),
            $this->createMock(SectionUpdateStruct::class),
        ];

        $updatedSection = $this->createMock(Section::class);
        $eventUpdatedSection = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('updateSection')->willReturn($updatedSection);

        $traceableEventDispatcher->addListener(BeforeUpdateSectionEventInterface::class, function (BeforeUpdateSectionEventInterface $event) use ($eventUpdatedSection) {
            $event->setUpdatedSection($eventUpdatedSection);
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedSection, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateSectionEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateSectionEventInterface::class, 0],
            [UpdateSectionEventInterface::class, 0],
        ]);
    }

    public function testAssignSectionToSubtreeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignSectionToSubtreeEventInterface::class,
            AssignSectionToSubtreeEventInterface::class
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
            [BeforeAssignSectionToSubtreeEventInterface::class, 0],
            [AssignSectionToSubtreeEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignSectionToSubtreeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignSectionToSubtreeEventInterface::class,
            AssignSectionToSubtreeEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeAssignSectionToSubtreeEventInterface::class, function (BeforeAssignSectionToSubtreeEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->assignSectionToSubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAssignSectionToSubtreeEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AssignSectionToSubtreeEventInterface::class, 0],
            [BeforeAssignSectionToSubtreeEventInterface::class, 0],
        ]);
    }

    public function testDeleteSectionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteSectionEventInterface::class,
            DeleteSectionEventInterface::class
        );

        $parameters = [
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteSectionEventInterface::class, 0],
            [DeleteSectionEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteSectionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteSectionEventInterface::class,
            DeleteSectionEventInterface::class
        );

        $parameters = [
            $this->createMock(Section::class),
        ];

        $innerServiceMock = $this->createMock(SectionServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteSectionEventInterface::class, function (BeforeDeleteSectionEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteSectionEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteSectionEventInterface::class, 0],
            [DeleteSectionEventInterface::class, 0],
        ]);
    }

    public function testCreateSectionEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateSectionEventInterface::class,
            CreateSectionEventInterface::class
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
            [BeforeCreateSectionEventInterface::class, 0],
            [CreateSectionEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateSectionResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateSectionEventInterface::class,
            CreateSectionEventInterface::class
        );

        $parameters = [
            $this->createMock(SectionCreateStruct::class),
        ];

        $section = $this->createMock(Section::class);
        $eventSection = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('createSection')->willReturn($section);

        $traceableEventDispatcher->addListener(BeforeCreateSectionEventInterface::class, function (BeforeCreateSectionEventInterface $event) use ($eventSection) {
            $event->setSection($eventSection);
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventSection, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateSectionEventInterface::class, 10],
            [BeforeCreateSectionEventInterface::class, 0],
            [CreateSectionEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateSectionStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateSectionEventInterface::class,
            CreateSectionEventInterface::class
        );

        $parameters = [
            $this->createMock(SectionCreateStruct::class),
        ];

        $section = $this->createMock(Section::class);
        $eventSection = $this->createMock(Section::class);
        $innerServiceMock = $this->createMock(SectionServiceInterface::class);
        $innerServiceMock->method('createSection')->willReturn($section);

        $traceableEventDispatcher->addListener(BeforeCreateSectionEventInterface::class, function (BeforeCreateSectionEventInterface $event) use ($eventSection) {
            $event->setSection($eventSection);
            $event->stopPropagation();
        }, 10);

        $service = new SectionService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createSection(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventSection, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateSectionEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateSectionEventInterface::class, 0],
            [CreateSectionEventInterface::class, 0],
        ]);
    }
}
