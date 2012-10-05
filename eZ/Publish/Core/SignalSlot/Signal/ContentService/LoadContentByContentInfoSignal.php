<?php
/**
 * LoadContentByContentInfoSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadContentByContentInfoSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 */
class LoadContentByContentInfoSignal extends Signal
{
    /**
     * ContentInfo
     *
     * @var eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public $contentInfo;

    /**
     * Languages
     *
     * @var mixed
     */
    public $languages;

    /**
     * VersionNo
     *
     * @var mixed
     */
    public $versionNo;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param mixed $languages
     * @param mixed $versionNo
     */
    public function __construct( $contentInfo, $languages, $versionNo )
    {
        $this->contentInfo = $contentInfo;
        $this->languages = $languages;
        $this->versionNo = $versionNo;
    }
}

