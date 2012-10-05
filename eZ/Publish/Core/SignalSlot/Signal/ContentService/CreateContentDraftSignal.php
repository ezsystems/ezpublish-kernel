<?php
/**
 * CreateContentDraftSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateContentDraftSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 */
class CreateContentDraftSignal extends Signal
{
    /**
     * ContentInfo
     *
     * @var eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public $contentInfo;

    /**
     * VersionInfo
     *
     * @var eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public $versionInfo;

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
     * @param eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param eZ\Publish\API\Repository\Values\User\User $user
     */
    public function __construct( $contentInfo, $versionInfo, $user )
    {
        $this->contentInfo = $contentInfo;
        $this->versionInfo = $versionInfo;
        $this->user = $user;
    }
}

