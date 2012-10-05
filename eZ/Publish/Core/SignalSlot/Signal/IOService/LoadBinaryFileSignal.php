<?php
/**
 * LoadBinaryFileSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\IOService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadBinaryFileSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\IOService
 */
class LoadBinaryFileSignal extends Signal
{
    /**
     * BinaryFileid
     *
     * @var mixed
     */
    public $binaryFileid;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param mixed $binaryFileid
     */
    public function __construct( $binaryFileid )
    {
        $this->binaryFileid = $binaryFileid;
    }
}

