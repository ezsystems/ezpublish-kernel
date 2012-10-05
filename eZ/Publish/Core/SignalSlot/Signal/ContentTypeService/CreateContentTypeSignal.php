<?php
/**
 * CreateContentTypeSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateContentTypeSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentTypeService
 */
class CreateContentTypeSignal extends Signal
{
    /**
     * ContentTypeCreateStruct
     *
     * @var eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct
     */
    public $contentTypeCreateStruct;

    /**
     * ContentTypeGroups
     *
     * @var mixed
     */
    public $contentTypeGroups;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     * @param mixed $contentTypeGroups
     */
    public function __construct( $contentTypeCreateStruct, $contentTypeGroups )
    {
        $this->contentTypeCreateStruct = $contentTypeCreateStruct;
        $this->contentTypeGroups = $contentTypeGroups;
    }
}

