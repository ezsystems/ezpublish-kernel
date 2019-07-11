<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Section;

use eZ\Publish\API\Repository\Events\BeforeEvent;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;

interface BeforeCreateSectionEvent extends BeforeEvent
{
    public function getSectionCreateStruct(): SectionCreateStruct;

    public function getSection(): Section;

    public function setSection(?Section $section): void;

    public function hasSection(): bool;
}
