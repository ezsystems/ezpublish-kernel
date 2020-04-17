<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;

abstract class SectionServiceDecorator implements SectionService
{
    /** @var \eZ\Publish\API\Repository\SectionService */
    protected $innerService;

    public function __construct(SectionService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createSection(SectionCreateStruct $sectionCreateStruct): Section
    {
        return $this->innerService->createSection($sectionCreateStruct);
    }

    public function updateSection(
        Section $section,
        SectionUpdateStruct $sectionUpdateStruct
    ): Section {
        return $this->innerService->updateSection($section, $sectionUpdateStruct);
    }

    public function loadSection(int $sectionId): Section
    {
        return $this->innerService->loadSection($sectionId);
    }

    public function loadSections(): iterable
    {
        return $this->innerService->loadSections();
    }

    public function loadSectionByIdentifier(string $sectionIdentifier): Section
    {
        return $this->innerService->loadSectionByIdentifier($sectionIdentifier);
    }

    public function countAssignedContents(Section $section): int
    {
        return $this->innerService->countAssignedContents($section);
    }

    public function isSectionUsed(Section $section): bool
    {
        return $this->innerService->isSectionUsed($section);
    }

    public function assignSection(
        ContentInfo $contentInfo,
        Section $section
    ): void {
        $this->innerService->assignSection($contentInfo, $section);
    }

    public function assignSectionToSubtree(
        Location $location,
        Section $section
    ): void {
        $this->innerService->assignSectionToSubtree($location, $section);
    }

    public function deleteSection(Section $section): void
    {
        $this->innerService->deleteSection($section);
    }

    public function newSectionCreateStruct(): SectionCreateStruct
    {
        return $this->innerService->newSectionCreateStruct();
    }

    public function newSectionUpdateStruct(): SectionUpdateStruct
    {
        return $this->innerService->newSectionUpdateStruct();
    }
}
