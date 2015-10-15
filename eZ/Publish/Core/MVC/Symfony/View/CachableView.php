<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

/**
 * A view that can be cached over HTTP.
 *
 * Should allow
 */
interface CachableView
{
    /**
     * Sets the cache as enabled/disabled.
     *
     * @param bool $cacheEnabled
     */
    public function setCacheEnabled($cacheEnabled);

    /**
     * Indicates if cache is enabled or not.
     *
     * @return bool
     */
    public function isCacheEnabled();
}
