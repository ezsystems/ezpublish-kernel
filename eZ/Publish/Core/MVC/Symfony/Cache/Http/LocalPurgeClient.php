<?php

/**
 * File containing the LocalPurgeClient class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * LocalPurgeClient emulates an Http PURGE request to be received by the Proxy Tag cache store.
 * Handy for single-serve using Symfony Proxy..
 */
class LocalPurgeClient implements PurgeClientInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\ContentPurger
     */
    protected $cacheStore;

    public function __construct(ContentPurger $cacheStore)
    {
        $this->cacheStore = $cacheStore;
    }

    public function purge($tags)
    {
        if (empty($tags)) {
            return;
        }

        $tags = array_map(
            function ($tag) {
                return is_numeric($tag) ? 'location-' . $tag : $tag;
            },
            (array)$tags
        );

        $purgeRequest = Request::create('http://localhost/', 'PURGE');
        $purgeRequest->headers->set('xkey', implode(' ', $tags));
        $this->cacheStore->purgeByRequest($purgeRequest);
    }

    public function purgeAll()
    {
        $this->cacheStore->purgeAllContent();
    }
}
