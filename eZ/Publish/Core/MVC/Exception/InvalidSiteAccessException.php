<?php
/**
 * File containing the InvalidSiteAccessException class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
    public function __construct( $siteAccess, array $siteAccessList, $matchType )
    {
        parent::__construct( "Invalid siteaccess '$siteAccess', matched by $matchType. Valid siteaccesses are " . implode( ', ', $siteAccessList ) );
    }
}
