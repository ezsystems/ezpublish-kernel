<?php
/**
 * LoadContentInfoByRemoteIdSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadContentInfoByRemoteIdSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 */
class LoadContentInfoByRemoteIdSignal extends Signal
{
    /**
     * RemoteId
     *
     * @var mixed
     */
    public $remoteId;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param mixed $remoteId
     */
    public function __construct( $remoteId )
    {
        $this->remoteId = $remoteId;
    }
}

