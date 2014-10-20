<?php
/**
 * File containing the Kernel class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class Kernel extends BaseKernel
{
    /**
     * Prefix for session name.
     */
    const SESSION_NAME_PREFIX = 'eZSESSID';

    /**
     * Returns the cache key under which the user hash will be stored.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return string
     */
    protected function getUserHashCacheKey( Request $request )
    {
        // There can be several session cookies (i.e. session name beginning by static::SESSION_NAME_PREFIX) for the same
        // domain / path as we can have several siteaccesses sharing the same session context.
        // Hence we concatenate session Ids together.
        $cacheKeyArray = array();
        foreach ( $request->cookies as $name => $value )
        {
            if ( $this->isSessionName( $name ) )
            {
                $cacheKeyArray[] = $value;
            }
        }

        return implode( '|', $cacheKeyArray );
    }
}
