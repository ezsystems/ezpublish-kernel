<?php

/**
 * SectionService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
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

    public function createSection(SectionCreateStruct $sectionCreateStruct)
    {
        return $this->service->createSection($sectionCreateStruct);
    }

    public function updateSection(Section $section, SectionUpdateStruct $sectionUpdateStruct)
    {
        return $this->service->updateSection($section, $sectionUpdateStruct);
    }

    public function loadSection($sectionId)
    {
        return $this->service->loadSection($sectionId);
    }

    public function loadSections()
    {
        return $this->service->loadSections();
    }

    public function loadSectionByIdentifier($sectionIdentifier)
    {
        return $this->service->loadSectionByIdentifier($sectionIdentifier);
    }

    public function countAssignedContents(Section $section)
    {
        return $this->service->countAssignedContents($section);
    }

    public function isSectionUsed(Section $section)
    {
        return $this->service->isSectionUsed($section);
    }

    public function assignSection(ContentInfo $contentInfo, Section $section)
    {
        return $this->service->assignSection($contentInfo, $section);
    }

    public function assignSectionToSubtree(Location $location, Section $section): void
    {
        $this->service->assignSectionToSubtree($location, $section);
    }

    public function deleteSection(Section $section)
    {
        return $this->service->deleteSection($section);
    }

    public function newSectionCreateStruct()
    {
        return $this->service->newSectionCreateStruct();
    }

    public function newSectionUpdateStruct()
    {
        return $this->service->newSectionUpdateStruct();
    }
}
