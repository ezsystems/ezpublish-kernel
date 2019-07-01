<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Section;

use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\Core\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeUpdateSectionEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Section */
    private $section;

    /** @var \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct */
    private $sectionUpdateStruct;

    /** @var \eZ\Publish\API\Repository\Values\Content\Section|null */
    private $updatedSection;

    public function __construct(Section $section, SectionUpdateStruct $sectionUpdateStruct)
    {
        $this->section = $section;
        $this->sectionUpdateStruct = $sectionUpdateStruct;
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
        if (!$this->hasUpdatedSection()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasUpdatedSection() or set it by setUpdatedSection() before you call getter.', Section::class));
        }

        return $this->updatedSection;
    }

    public function setUpdatedSection(?Section $updatedSection): void
    {
        $this->updatedSection = $updatedSection;
    }

    public function hasUpdatedSection(): bool
    {
        return $this->updatedSection instanceof Section;
    }
}
