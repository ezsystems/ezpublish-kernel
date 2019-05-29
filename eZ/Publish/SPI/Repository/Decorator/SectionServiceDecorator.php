<?php

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
    /**
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $innerService;

    public function __construct(SectionService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createSection(SectionCreateStruct $sectionCreateStruct)
    {
        $this->innerService->createSection($sectionCreateStruct);
    }

    public function updateSection(
        Section $section,
        SectionUpdateStruct $sectionUpdateStruct
    ) {
        $this->innerService->updateSection($section, $sectionUpdateStruct);
    }

    public function loadSection($sectionId)
    {
        $this->innerService->loadSection($sectionId);
    }

    public function loadSections()
    {
        $this->innerService->loadSections();
    }

    public function loadSectionByIdentifier($sectionIdentifier)
    {
        $this->innerService->loadSectionByIdentifier($sectionIdentifier);
    }

    public function countAssignedContents(Section $section)
    {
        $this->innerService->countAssignedContents($section);
    }

    public function isSectionUsed(Section $section)
    {
        $this->innerService->isSectionUsed($section);
    }

    public function assignSection(
        ContentInfo $contentInfo,
        Section $section
    ) {
        $this->innerService->assignSection($contentInfo, $section);
    }

    public function assignSectionToSubtree(
        Location $location,
        Section $section
    ): void {
        $this->innerService->assignSectionToSubtree($location, $section);
    }

    public function deleteSection(Section $section)
    {
        $this->innerService->deleteSection($section);
    }

    public function newSectionCreateStruct()
    {
        $this->innerService->newSectionCreateStruct();
    }

    public function newSectionUpdateStruct()
    {
        $this->innerService->newSectionUpdateStruct();
    }
}
