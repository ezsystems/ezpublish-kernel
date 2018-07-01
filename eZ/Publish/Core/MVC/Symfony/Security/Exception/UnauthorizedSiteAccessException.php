<?php

/**
 * File containing the UnauthorizedSiteAccessException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
    public function __construct(SiteAccess $siteAccess, $username, Exception $previous = null)
    {
        parent::__construct("User '$username' doesn't have user/login permission to SiteAccess '$siteAccess->name'", $previous);
    }
}
