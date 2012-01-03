<?php
/**
 * Contains FailedLogin Exception implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User\Exception;
use ezp\Base\Exception\Unauthorized,
    Exception as PHPException;

/**
 * FailedLogin Exception implementation
 *
 * Use:
 *   throw new FailedLogin();
 *
 */
class FailedLogin extends Unauthorized
{
    /**
     * Generates: Login required to get access to 'login'
     *
     * @todo Find a more suitable message
     * @param \Exception|null $previous
     */
    public function __construct( PHPException $previous = null )
    {
        parent::__construct( "login", $previous );
    }
}
