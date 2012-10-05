<?php
/**
 * DeleteBinaryFileSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\IOService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * DeleteBinaryFileSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\IOService
 */
class DeleteBinaryFileSignal extends Signal
{
    /**
     * BinaryFile
     *
     * @var eZ\Publish\API\Repository\Values\IO\BinaryFile
     */
    public $binaryFile;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\IO\BinaryFile $binaryFile
     */
    public function __construct( $binaryFile )
    {
        $this->binaryFile = $binaryFile;
    }
}

