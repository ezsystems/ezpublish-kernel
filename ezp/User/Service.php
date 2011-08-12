<?php
/**
 * File contains User Service
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User;
use ezp\Base\Service as BaseService,
    InvalidArgumentException,
    ezp\Persistence\ValueObject;

/**
 * User Service, extends repository with user specific operations
 *
 */
class Service extends BaseService
{
    /**
     * Get an User object by id
     *
     * @param int $id
     * @return User
     * @throws InvalidArgumentException
     */
    public function load( $id )
    {
        $user = $this->handler->userHandler()->load( (int)$id );
        // @FIXME: Shouldn't this be a NotFound exception instead?
        if ( !$user )
            throw new InvalidArgumentException( "Could not find 'User' with id: {$id}" );
        return $user;
    }

    protected function buildDomainObject( ValueObject $vo )
    {

    }
}
