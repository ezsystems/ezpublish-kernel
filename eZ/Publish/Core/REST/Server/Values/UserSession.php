<?php
/**
 * File containing the UserList class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * User list view model
 */
class UserSession extends RestValue
{
    /**
     * User
     *
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    public $user;

    /**
     * Session name
     *
     * @var string
     */
    public $sessionName;

    /**
     * Session ID
     * @var string
     */
    public $identifier;

    /**
     * CSRF token name
     * @var string
     */
    public $csrfParam;

    /**
     * CSRF token value
     * @var string
     */
    public $csrfToken;


    /**
     * Construct
     *
     * @param \eZ\Publish\Core\REST\Server\Values\RestUser[] $users
     * @param string $path
     */
    public function __construct( $user, $name, $identifier, $csrfParam, $csrfToken )
    {
        $this->user = $user;
        $this->sessionName = $name;
        $this->identifier = $identifier;
        $this->csrfParam = $csrfParam;
        $this->csrfToken = $csrfToken;
    }
}
