<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SymfonyCache;

use FOS\HttpCache\SymfonyCache\UserContextSubscriber as BaseUserContextSubscriber;
use Symfony\Component\HttpFoundation\Request;

/**
 * Extends UserContextSubscriber from FOSHttpCache to include original request.
 *
 * {@inheritdoc}
 *
 * @deprecated since 5.4. Will be removed in future 7.x FT release. Use FOSHttpCacheBundle user context feature instead.
 */
class UserContextSubscriber extends BaseUserContextSubscriber
{
    protected function cleanupHashLookupRequest(Request $hashLookupRequest, Request $originalRequest)
    {
        parent::cleanupHashLookupRequest($hashLookupRequest, $originalRequest);
        // Embed the original request as we need it to match the SiteAccess.
        $hashLookupRequest->attributes->set('_ez_original_request', $originalRequest);
    }
}
