<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Section;

use eZ\Publish\API\Repository\Events\Section\UpdateSectionEvent as UpdateSectionEventInterface;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use Symfony\Contracts\EventDispatcher\Event;

final class UpdateSectionEvent extends Event implements UpdateSectionEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Section */
    private $section;

    /** @var \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct */
    private $sectionUpdateStruct;

    /** @var \eZ\Publish\API\Repository\Values\Content\Section */
    private $updatedSection;

    public function __construct(
        Section $updatedSection,
        Section $section,
        SectionUpdateStruct $sectionUpdateStruct
    ) {
        $this->section = $section;
        $this->sectionUpdateStruct = $sectionUpdateStruct;
        $this->updatedSection = $updatedSection;
    }

    public function getSection(): Section
    {
        return $this->section;
    }

    public function getSectionUpdateStruct(): SectionUpdateStruct
    {
        return $this->sectionUpdateStruct;
    }

    public function getUpdatedSection(): Section
    {
        return $this->updatedSection;
    }
}
