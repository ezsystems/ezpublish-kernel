<?php
/**
 * LoadContentTypeSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadContentTypeSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentTypeService
 */
class LoadContentTypeSignal extends Signal
{
    /**
     * ContentTypeId
     *
     * @var mixed
     */
    public $contentTypeId;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param mixed $contentTypeId
     */
    public function __construct( $contentTypeId )
    {
        $this->contentTypeId = $contentTypeId;
    }
}

