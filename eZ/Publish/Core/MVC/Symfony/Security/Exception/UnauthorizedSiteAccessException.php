<?php
/**
 * File containing the InsufficentAccessPermissionException class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Exception;

use Exception;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * This exception is thrown when a user tries to authenticate against a SiteAccess
 * for which he doesn't have user/login permission for.
 */
class UnauthorizedSiteAccessException extends AccessDeniedException
{
    public function __construct( SiteAccess $siteAccess, $username, Exception $previous = null )
    {
        parent::__construct( "User '$username' doesn't have user/login permission to SiteAccess '$siteAccess->name'", $previous );
    }
}
