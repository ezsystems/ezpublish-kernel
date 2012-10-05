<?php
/**
 * LoadContentTypeGroupSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadContentTypeGroupSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentTypeService
 */
class LoadContentTypeGroupSignal extends Signal
{
    /**
     * ContentTypeGroupId
     *
     * @var mixed
     */
    public $contentTypeGroupId;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param mixed $contentTypeGroupId
     */
    public function __construct( $contentTypeGroupId )
    {
        $this->contentTypeGroupId = $contentTypeGroupId;
    }
}

