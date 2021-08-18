<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\SPI\Persistence\URL\Handler as URLHandlerInterface;
use eZ\Publish\SPI\Persistence\URL\URLUpdateStruct;

/**
 * SPI cache for URL Handler.
 *
 * @see \eZ\Publish\SPI\Persistence\URL\Handler
 */
class URLHandler extends AbstractHandler implements URLHandlerInterface
{
    private const URL_TAG = 'url';
    private const CONTENT_TAG = 'content';

    /**
     * {@inheritdoc}
     */
    public function updateUrl($id, URLUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, [
            'url' => $id,
            'struct' => $struct,
        ]);

        $url = $this->persistenceHandler->urlHandler()->updateUrl($id, $struct);

        $this->cache->invalidateTags([
            $this->tagGenerator->generate(self::URL_TAG, [$id]),
        ]);

        if ($struct->url !== null) {
            $this->cache->invalidateTags(array_map(function ($id) {
                return $this->tagGenerator->generate(self::CONTENT_TAG, [$id]);
            }, $this->persistenceHandler->urlHandler()->findUsages($id)));
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function find(URLQuery $query)
    {
        $this->logger->logCall(__METHOD__, [
            'query' => $query,
        ]);

        return $this->persistenceHandler->urlHandler()->find($query);
    }

    /**
     * {@inheritdoc}
     */
    public function loadById($id)
    {
        $cacheItem = $this->cache->getItem(
            $this->tagGenerator->generate(self::URL_TAG, [$id], true)
        );

        $url = $cacheItem->get();
        if ($cacheItem->isHit()) {
            return $url;
        }

        $this->logger->logCall(__METHOD__, ['url' => $id]);
        $url = $this->persistenceHandler->urlHandler()->loadById($id);

        $cacheItem->set($url);
        $cacheItem->tag([
            $this->tagGenerator->generate(self::URL_TAG, [$id]),
        ]);
        $this->cache->save($cacheItem);

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByUrl($url)
    {
        $this->logger->logCall(__METHOD__, ['url' => $url]);

        return $this->persistenceHandler->urlHandler()->loadByUrl($url);
    }

    /**
     * {@inheritdoc}
     */
    public function findUsages($id)
    {
        $this->logger->logCall(__METHOD__, ['url' => $id]);

        return $this->persistenceHandler->urlHandler()->findUsages($id);
    }
}
