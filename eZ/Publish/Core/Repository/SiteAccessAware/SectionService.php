<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\SectionService as SectionServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

/**
 * SectionService for SiteAccessAware layer.
 *
 * Currently does nothing but hand over calls to aggregated service.
 */
class SectionService implements SectionServiceInterface
{
    /** @var \eZ\Publish\API\Repository\SectionService */
    protected $service;

    /**
     * Construct service object from aggregated service.
     *
     * @param \eZ\Publish\API\Repository\SectionService $service
     */
    public function __construct(
        SectionServiceInterface $service
    ) {
        $this->service = $service;
    }

    public function createSection(SectionCreateStruct $sectionCreateStruct): Section
    {
        return $this->service->createSection($sectionCreateStruct);
    }

    public function updateSection(Section $section, SectionUpdateStruct $sectionUpdateStruct): Section
    {
        return $this->service->updateSection($section, $sectionUpdateStruct);
    }

    public function loadSection(int $sectionId): Section
    {
        return $this->service->loadSection($sectionId);
    }

    public function loadSections(): iterable
    {
        return $this->service->loadSections();
    }

    public function loadSectionByIdentifier(string $sectionIdentifier): Section
    {
        return $this->service->loadSectionByIdentifier($sectionIdentifier);
    }

    public function countAssignedContents(Section $section): int
    {
        return $this->service->countAssignedContents($section);
    }

    public function isSectionUsed(Section $section): bool
    {
        return $this->service->isSectionUsed($section);
    }

    public function assignSection(ContentInfo $contentInfo, Section $section): void
    {
        $this->service->assignSection($contentInfo, $section);
    }

    public function assignSectionToSubtree(Location $location, Section $section): void
    {
        $this->service->assignSectionToSubtree($location, $section);
    }

    public function deleteSection(Section $section): void
    {
        $this->service->deleteSection($section);
    }

    public function newSectionCreateStruct(): SectionCreateStruct
    {
        return $this->service->newSectionCreateStruct();
    }

    public function newSectionUpdateStruct(): SectionUpdateStruct
    {
        return $this->service->newSectionUpdateStruct();
    }
}
