<?php
/**
 * File containing the UserGroupRefList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

/**
 * User group list view model
 */
class UserGroupRefList
{
    /**
     * User groups
     *
     * @var \eZ\Publish\API\Repository\Values\User\UserGroup[]
     */
    public $userGroups;

    /**
     * Path which was used to fetch the list of user groups
     *
     * @var string
     */
    public $path;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup[] $userGroups
     * @param string $path
     */
    public function __construct( array $userGroups, $path )
    {
        $this->userGroups = $userGroups;
        $this->path = $path;
    }
}
