<?php
/**
 * UpdateSectionSignal class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\SectionService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateSectionSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\SectionService
 */
class UpdateSectionSignal extends Signal
{
    /**
     * SectionId
     *
     * @var mixed
     */
    public $sectionId;
}
