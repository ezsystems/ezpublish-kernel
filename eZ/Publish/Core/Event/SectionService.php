<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

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
use eZ\Publish\SPI\Repository\Decorator\SectionServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SectionService extends SectionServiceDecorator
{
    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(
        SectionServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function createSection(SectionCreateStruct $sectionCreateStruct)
    {
        $eventData = [$sectionCreateStruct];

        $beforeEvent = new BeforeCreateSectionEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getSection();
        }

        $section = $beforeEvent->hasSection()
            ? $beforeEvent->getSection()
            : $this->innerService->createSection($sectionCreateStruct);

        $this->eventDispatcher->dispatch(new CreateSectionEvent($section, ...$eventData));

        return $section;
    }

    public function updateSection(
        Section $section,
        SectionUpdateStruct $sectionUpdateStruct
    ) {
        $eventData = [
            $section,
            $sectionUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateSectionEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getUpdatedSection();
        }

        $updatedSection = $beforeEvent->hasUpdatedSection()
            ? $beforeEvent->getUpdatedSection()
            : $this->innerService->updateSection($section, $sectionUpdateStruct);

        $this->eventDispatcher->dispatch(new UpdateSectionEvent($updatedSection, ...$eventData));

        return $updatedSection;
    }

    public function assignSection(
        ContentInfo $contentInfo,
        Section $section
    ): void {
        $eventData = [
            $contentInfo,
            $section,
        ];

        $beforeEvent = new BeforeAssignSectionEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return;
        }

        $this->innerService->assignSection($contentInfo, $section);

        $this->eventDispatcher->dispatch(new AssignSectionEvent(...$eventData));
    }

    public function assignSectionToSubtree(
        Location $location,
        Section $section
    ): void {
        $eventData = [
            $location,
            $section,
        ];

        $beforeEvent = new BeforeAssignSectionToSubtreeEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return;
        }

        $this->innerService->assignSectionToSubtree($location, $section);

        $this->eventDispatcher->dispatch(new AssignSectionToSubtreeEvent(...$eventData));
    }

    public function deleteSection(Section $section): void
    {
        $eventData = [$section];

        $beforeEvent = new BeforeDeleteSectionEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteSection($section);

        $this->eventDispatcher->dispatch(new DeleteSectionEvent(...$eventData));
    }
}
