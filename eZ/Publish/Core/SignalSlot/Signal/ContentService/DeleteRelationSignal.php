<?php
/**
 * DeleteRelationSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * DeleteRelationSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 */
class DeleteRelationSignal extends Signal
{
    /**
     * SourceVersion
     *
     * @var eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public $sourceVersion;

    /**
     * DestinationContent
     *
     * @var eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public $destinationContent;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\VersionInfo $sourceVersion
     * @param eZ\Publish\API\Repository\Values\Content\ContentInfo $destinationContent
     */
    public function __construct( $sourceVersion, $destinationContent )
    {
        $this->sourceVersion = $sourceVersion;
        $this->destinationContent = $destinationContent;
    }
}

