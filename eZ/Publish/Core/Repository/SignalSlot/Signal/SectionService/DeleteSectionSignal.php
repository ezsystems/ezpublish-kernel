<?php

/**
 * DeleteSectionSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Signal\SectionService;

use eZ\Publish\Core\Repository\SignalSlot\Signal;

/**
 * DeleteSectionSignal class.
 */
class DeleteSectionSignal extends Signal
{
    /**
     * SectionId.
     *
     * @var mixed
     */
    public $sectionId;
}
