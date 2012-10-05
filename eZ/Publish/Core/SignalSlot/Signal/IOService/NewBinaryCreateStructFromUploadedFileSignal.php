<?php
/**
 * NewBinaryCreateStructFromUploadedFileSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\IOService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * NewBinaryCreateStructFromUploadedFileSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\IOService
 */
class NewBinaryCreateStructFromUploadedFileSignal extends Signal
{
    /**
     * UploadedFile
     *
     * @var mixed
     */
    public $uploadedFile;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param mixed $uploadedFile
     */
    public function __construct( $uploadedFile )
    {
        $this->uploadedFile = $uploadedFile;
    }
}

