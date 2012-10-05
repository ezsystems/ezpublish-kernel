<?php
/**
 * LoadTranslationInfosSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadTranslationInfosSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 */
class LoadTranslationInfosSignal extends Signal
{
    /**
     * ContentInfo
     *
     * @var eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public $contentInfo;

    /**
     * Filter
     *
     * @var mixed
     */
    public $filter;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param mixed $filter
     */
    public function __construct( $contentInfo, $filter )
    {
        $this->contentInfo = $contentInfo;
        $this->filter = $filter;
    }
}

