<?php
/**
 * UpdateContentTypeGroupSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateContentTypeGroupSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentTypeService
 */
class UpdateContentTypeGroupSignal extends Signal
{
    /**
     * ContentTypeGroup
     *
     * @var eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public $contentTypeGroup;

    /**
     * ContentTypeGroupUpdateStruct
     *
     * @var eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    public $contentTypeGroupUpdateStruct;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     * @param eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct
     */
    public function __construct( $contentTypeGroup, $contentTypeGroupUpdateStruct )
    {
        $this->contentTypeGroup = $contentTypeGroup;
        $this->contentTypeGroupUpdateStruct = $contentTypeGroupUpdateStruct;
    }
}

