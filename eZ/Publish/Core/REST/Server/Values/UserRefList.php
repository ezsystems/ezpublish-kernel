<?php
/**
 * File containing the UserRefList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * User list view model
 */
class UserRefList extends RestValue
{
    /**
     * Users
     *
     * @var \eZ\Publish\Core\REST\Server\Values\RestUser[]
     */
    public $users;

    /**
     * Path which was used to fetch the list of users
     *
     * @var string
     */
    public $path;

    /**
     * Construct
     *
     * @param \eZ\Publish\Core\REST\Server\Values\RestUser[] $users
     * @param string $path
     */
    public function __construct( array $users, $path )
    {
        $this->users = $users;
        $this->path = $path;
    }
}
