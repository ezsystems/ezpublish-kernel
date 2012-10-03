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
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * Version view model
 */
class Version extends RestValue
{
    /**
     * Version info
     *
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public $versionInfo;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     */
    public function __construct( VersionInfo $versionInfo )
    {
        $this->versionInfo = $versionInfo;
    }
}
