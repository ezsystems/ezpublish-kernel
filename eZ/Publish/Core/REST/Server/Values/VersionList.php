<?php
/**
 * File containing the VersionList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

/**
 * Version list view model
 */
class VersionList
{
    /**
     * Versions
     *
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo[]
     */
    public $versions;

    /**
     * Content ID to which these versions belong to
     *
     * @var mixed
     */
    public $contentId;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo[] $versions
     * @param mixed $contentId
     */
    public function __construct( array $versions, $contentId )
    {
        $this->versions = $versions;
        $this->contentId = $contentId;
    }
}
