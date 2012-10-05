<?php
/**
 * CreateBinaryFileSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\IOService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateBinaryFileSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\IOService
 */
class CreateBinaryFileSignal extends Signal
{
    /**
     * BinaryFileCreateStruct
     *
     * @var eZ\Publish\API\Repository\Values\IO\BinaryFileCreateStruct
     */
    public $binaryFileCreateStruct;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\IO\BinaryFileCreateStruct $binaryFileCreateStruct
     */
    public function __construct( $binaryFileCreateStruct )
    {
        $this->binaryFileCreateStruct = $binaryFileCreateStruct;
    }
}

