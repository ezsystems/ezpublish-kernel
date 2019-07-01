<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Section;

use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\Core\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeCreateSectionEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct */
    private $sectionCreateStruct;

    /** @var \eZ\Publish\API\Repository\Values\Content\Section|null */
    private $section;

    public function __construct(SectionCreateStruct $sectionCreateStruct)
    {
        $this->sectionCreateStruct = $sectionCreateStruct;
    }

    public function getSectionCreateStruct(): SectionCreateStruct
    {
        return $this->sectionCreateStruct;
    }

    public function getSection(): Section
    {
        if (!$this->hasSection()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasSection() or set it by setSection() before you call getter.', Section::class));
        }

        return $this->section;
    }

    public function setSection(?Section $section): void
    {
        $this->section = $section;
    }

    public function hasSection(): bool
    {
        return $this->section instanceof Section;
    }
}
