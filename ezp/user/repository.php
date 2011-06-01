<?php
/**
 * File containing the ezp\User\Repository class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package User
 */

/**
 * The eZ Content Repository, object that manages the Content Domain objects
 * @package User
 */
namespace ezp\User;
use ezp\Repository as BaseRepository;

class Repository extends BaseRepository
{
    /**
     * Loads a content from it's $id
     *
     * @param int $id
     *
     * @return User
     */
    public function loadUser( $id )
    {

    }

    public function currentUser()
    {
        return new User();
    }
}
?>