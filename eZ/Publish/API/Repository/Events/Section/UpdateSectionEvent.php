<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Section;

use eZ\Publish\API\Repository\Events\AfterEvent;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;

interface UpdateSectionEvent extends AfterEvent
{
    public function getSection(): Section;

    public function getSectionUpdateStruct(): SectionUpdateStruct;

    public function getUpdatedSection(): Section;
}
