<?php

/**
 * File containing the InvalidSiteAccessException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Exception;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface;
use RuntimeException;

/**
 * This exception is thrown if an invalid SiteAccess was matched.
 */
class InvalidSiteAccessException extends RuntimeException
{
    /**
     * @param string $siteAccess The invalid SiteAccess
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface $siteAccessProvider
     * @param string $matchType How $siteAccess was matched
     * @param bool $debug If true, Symfony environment is a debug one (like 'dev')
     */
    public function __construct(
        string $siteAccess,
        SiteAccessProviderInterface $siteAccessProvider,
        string $matchType,
        bool $debug = false
    ) {
        $message = "Invalid SiteAccess '$siteAccess', matched by $matchType.";
        if ($debug) {
            $siteAccessList = array_column(iterator_to_array($siteAccessProvider->getSiteAccesses()), 'name');
            $message .= ' Valid SiteAccesses are ' . implode(', ', $siteAccessList);
        }

        parent::__construct($message);
    }
}
