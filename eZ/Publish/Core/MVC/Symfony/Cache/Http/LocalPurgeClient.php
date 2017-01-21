<?php

/**
 * File containing the LocalPurgeClient class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface;
use eZ\Publish\Core\MVC\Symfony\Cache\TagAwarePurgeClientInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * LocalPurgeClient emulates an Http PURGE request to be received by the Proxy Tag cache store.
 * Handy for single-serve using Symfony Proxy..
 */
class LocalPurgeClient implements PurgeClientInterface, TagAwarePurgeClientInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\ContentPurger
     */
    protected $cacheStore;

    public function __construct(ContentPurger $cacheStore)
    {
        $this->cacheStore = $cacheStore;
    }

    /**
     * {@inheritdoc}
     */
    public function purge($locationIds)
    {
        if (empty($locationIds)) {
            return;
        }

        $this->purgeByTags(
            array_map(
                function ($locationId) {
                    return 'location-' . $locationId;
                },
                (array)$locationIds
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function purgeByTags(array $tags)
    {
        if (empty($tags)) {
            return;
        }

        $purgeRequest = Request::create('http://localhost/', 'PURGE');
        $purgeRequest->headers->set('xkey', implode(' ', $tags));
        $this->cacheStore->purgeByRequest($purgeRequest);
    }

    /**
     * {@inheritdoc}
     */
    public function purgeAll()
    {
        $this->cacheStore->purgeAllContent();
    }
}
