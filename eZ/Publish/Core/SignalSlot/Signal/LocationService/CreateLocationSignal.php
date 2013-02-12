<?php
/**
 * CreateLocationSignal class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LocationService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateLocationSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\LocationService
 */
class CreateLocationSignal extends Signal
{
    /**
     * ContentId
     *
     * @var mixed
     */
    public $contentId;

    /**
     * Location ID
     *
     * @var mixed
     */
    public $locationId;
}
