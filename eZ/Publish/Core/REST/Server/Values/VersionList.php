<?php
/**
 * File containing the VersionList class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * Version list view model
 */
class VersionList extends RestValue
{
    /**
     * Versions
     *
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo[]
     */
    public $versions;

    /**
     * Path used to retrieve this version list
     *
     * @var string
     */
    public $path;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo[] $versions
     * @param string $path
     */
    public function __construct( array $versions, $path )
    {
        $this->versions = $versions;
        $this->path = $path;
    }
}
