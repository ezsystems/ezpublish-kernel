<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot\Signal\SectionService;

use eZ\Publish\Core\SignalSlot\Signal;

class AssignSectionToSubtreeSignal extends Signal
{
    /**
     * LocationtId.
     *
     * @var int
     */
    public $locationId;

    /**
     * SectionId.
     *
     * @var int
     */
    public $sectionId;
}
