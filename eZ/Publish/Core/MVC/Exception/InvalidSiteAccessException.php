<?php

/**
 * File containing the InvalidSiteAccessException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Exception;

use RuntimeException;

/**
 * This exception is thrown if an invalid siteaccess was matched.
 */
class InvalidSiteAccessException extends RuntimeException
{
    /**
     * @param string $siteAccess The invalid siteaccess
     * @param array $siteAccessList All valid siteaccesses, as a regular array
     * @param string $matchType How $siteAccess was matched
     */
    public function __construct($siteAccess, array $siteAccessList, $matchType)
    {
        parent::__construct("Invalid siteaccess '$siteAccess', matched by $matchType. Valid siteaccesses are " . implode(', ', $siteAccessList));
    }
}
