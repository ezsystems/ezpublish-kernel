<?php
/**
 * LoadContentDraftsSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadContentDraftsSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 */
class LoadContentDraftsSignal extends Signal
{
    /**
     * User
     *
     * @var eZ\Publish\API\Repository\Values\User\User
     */
    public $user;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\User\User $user
     */
    public function __construct( $user )
    {
        $this->user = $user;
    }
}

