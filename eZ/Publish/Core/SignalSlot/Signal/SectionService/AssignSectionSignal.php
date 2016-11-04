<?php

/**
 * AssignSectionSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\SectionService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * AssignSectionSignal class.
 */
class AssignSectionSignal extends Signal
{
    /**
     * ContentId.
     *
     * @var mixed
     */
    public $contentId;

    /**
     * SectionId.
     *
     * @var mixed
     */
    public $sectionId;
}
