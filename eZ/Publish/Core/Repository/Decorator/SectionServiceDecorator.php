<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;

abstract class SectionServiceDecorator implements SectionService
{
    /**
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\SectionService $service
     */
    public function __construct(SectionService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function createSection(SectionCreateStruct $sectionCreateStruct)
    {
        return $this->service->createSection($sectionCreateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function updateSection(Section $section, SectionUpdateStruct $sectionUpdateStruct)
    {
        return $this->service->updateSection($section, $sectionUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function loadSection($sectionId)
    {
        return $this->service->loadSection($sectionId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadSections()
    {
        return $this->service->loadSections();
    }

    /**
     * {@inheritdoc}
     */
    public function loadSectionByIdentifier($sectionIdentifier)
    {
        return $this->service->loadSectionByIdentifier($sectionIdentifier);
    }

    /**
     * {@inheritdoc}
     */
    public function countAssignedContents(Section $section)
    {
        return $this->service->countAssignedContents($section);
    }

    /**
     * {@inheritdoc}
     */
    public function isSectionUsed(Section $section)
    {
        return $this->service->isSectionUsed($section);
    }

    /**
     * {@inheritdoc}
     */
    public function assignSection(ContentInfo $contentInfo, Section $section)
    {
        return $this->service->assignSection($contentInfo, $section);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSection(Section $section)
    {
        return $this->service->deleteSection($section);
    }

    /**
     * {@inheritdoc}
     */
    public function newSectionCreateStruct()
    {
        return $this->service->newSectionCreateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function newSectionUpdateStruct()
    {
        return $this->service->newSectionUpdateStruct();
    }
}
