<?php

/**
 * File containing the RequestAwarePurger interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface allowing implementor (cache Store) to purge Http cache from a request object.
 *
 * @deprecated since 6.8. Use RequestAwarePurger from the platform-http-cache package.
 */
interface RequestAwarePurger
{
    /**
     * Purges data from $request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool True if purge was successful. False otherwise
     */
    public function purgeByRequest(Request $request);
}
