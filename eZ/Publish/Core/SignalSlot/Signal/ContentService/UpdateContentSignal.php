<?php
/**
 * UpdateContentSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateContentSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 */
class UpdateContentSignal extends Signal
{
    /**
     * VersionInfo
     *
     * @var eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public $versionInfo;

    /**
     * ContentUpdateStruct
     *
     * @var eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    public $contentUpdateStruct;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct $contentUpdateStruct
     */
    public function __construct( $versionInfo, $contentUpdateStruct )
    {
        $this->versionInfo = $versionInfo;
        $this->contentUpdateStruct = $contentUpdateStruct;
    }
}

