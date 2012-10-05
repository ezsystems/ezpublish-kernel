<?php
/**
 * LoadContentSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadContentSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 */
class LoadContentSignal extends Signal
{
    /**
     * ContentId
     *
     * @var mixed
     */
    public $contentId;

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
     * @param mixed $contentId
     * @param mixed $languages
     * @param mixed $versionNo
     */
    public function __construct( $contentId, $languages, $versionNo )
    {
        $this->contentId = $contentId;
        $this->languages = $languages;
        $this->versionNo = $versionNo;
    }
}

