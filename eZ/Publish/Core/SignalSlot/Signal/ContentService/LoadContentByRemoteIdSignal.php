<?php
/**
 * LoadContentByRemoteIdSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadContentByRemoteIdSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 */
class LoadContentByRemoteIdSignal extends Signal
{
    /**
     * RemoteId
     *
     * @var mixed
     */
    public $remoteId;

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
     * @param mixed $remoteId
     * @param mixed $languages
     * @param mixed $versionNo
     */
    public function __construct( $remoteId, $languages, $versionNo )
    {
        $this->remoteId = $remoteId;
        $this->languages = $languages;
        $this->versionNo = $versionNo;
    }
}

