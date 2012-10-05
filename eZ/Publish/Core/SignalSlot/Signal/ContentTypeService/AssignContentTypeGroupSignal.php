<?php
/**
 * AssignContentTypeGroupSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * AssignContentTypeGroupSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentTypeService
 */
class AssignContentTypeGroupSignal extends Signal
{
    /**
     * ContentType
     *
     * @var eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public $contentType;

    /**
     * ContentTypeGroup
     *
     * @var eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public $contentTypeGroup;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    public function __construct( $contentType, $contentTypeGroup )
    {
        $this->contentType = $contentType;
        $this->contentTypeGroup = $contentTypeGroup;
    }
}

