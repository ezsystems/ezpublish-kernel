<?php
/**
 * File containing the Version class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;

/**
 * Version view model
 */
class Version
{
    /**
     * Version info
     *
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public $versionInfo;

    /**
     * Content ID to which this version belongs to
     *
     * @var mixed
     */
    public $contentId;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param mixed $contentId
     */
    public function __construct( VersionInfo $versionInfo, $contentId )
    {
        $this->versionInfo = $versionInfo;
        $this->contentId = $contentId;
    }
}
