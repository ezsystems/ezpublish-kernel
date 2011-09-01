<?php
/**
 * File containing user location class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User;
use ezp\Content\Location as ContentLocation,
    ezp\User,
    ezp\User\Location as UserLocationAbstract;

/**
 * This class represents a User location item
 */
class UserLocation extends UserLocationAbstract
{
    /**
     * @var \ezp\User The User assigned to this location
     */
    protected $user;

    /**
     * Creates and setups User location object
     *
     * @access private Use {@link \ezp\User\Service::assignGroupLocation()} to create objects of this type
     * @param \ezp\Content\Location $location
     * @param \ezp\User $user
     */
    public function __construct( ContentLocation $location, User $user )
    {
        $this->user = $user;
        parent::__construct( $location );
    }

    /**
     * Get user assigned to this location
     *
     * @return \ezp\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
