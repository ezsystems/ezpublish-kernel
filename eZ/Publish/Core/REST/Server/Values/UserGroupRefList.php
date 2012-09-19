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
     * User ID whose groups are the ones in the list
     *
     * @var mixed
     */
    public $userId;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup[] $userGroups
     * @param string $path
     * @param mixed $userId
     */
    public function __construct( array $userGroups, $path, $userId = null )
    {
        $this->userGroups = $userGroups;
        $this->path = $path;
        $this->userId = $userId;
    }
}
