<?php

/**
 * UpdateSectionSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\SectionService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateSectionSignal class.
 */
class UpdateSectionSignal extends Signal
{
    /**
     * SectionId.
     *
     * @var mixed
     */
    public $sectionId;
}
