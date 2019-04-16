<?php

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler as UrlWildcardHandlerInterface;

class UrlWildcardHandler extends AbstractHandler implements UrlWildcardHandlerInterface
{
    /**
     * @see \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler::create
     */
    public function create($sourceUrl, $destinationUrl, $forward = false)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'sourceUrl' => $sourceUrl,
                'destinationUrl' => $destinationUrl,
                'forward' => $forward,
            ]
        );

        $urlWildcard = $this->persistenceHandler->urlWildcardHandler()->create($sourceUrl, $destinationUrl, $forward);

        // need to clear lists of UrlWildcards cached due to loadAll()
        $this->cache->invalidateTags(['ez-urlWildcard-all']);

        return $urlWildcard;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler::remove
     */
    public function remove($id)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'id' => $id,
            ]
        );

        $urlWildcard = $this->load($id);

        $this->persistenceHandler->urlWildcardHandler()->remove($urlWildcard->id);

        // need to clear lists of UrlWildcards cached due to loadAll()
        $this->cache->invalidateTags(['ez-urlWildcard-all']);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler::load
     */
    public function load($id)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'id' => $id,
            ]
        );

        $cacheItem = $this->cache->getItem('ez-urlWildcard-id-' . $id);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $urlWildcard = $this->persistenceHandler->urlWildcardHandler()->load($id);

        $cacheItem->set($urlWildcard);
        $cacheItem->tag($this->getCacheTags([$urlWildcard]));
        $this->cache->save($cacheItem);

        $cacheItemBySource = $this->cache->getItem('ez-urlWildcard-source-' . $this->escapeForCacheKey($urlWildcard->sourceUrl));
        $cacheItemBySource->set($urlWildcard);
        $cacheItem->tag($this->getCacheTags([$urlWildcard]));
        $this->cache->save($cacheItemBySource);

        return $urlWildcard;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler::loadAll
     */
    public function loadAll($offset = 0, $limit = -1)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'offset' => $offset,
                'limit' => $limit,
            ]
        );

        $cacheItem = $this->cache->getItem('ez-urlWildcard-all-' . $offset . '-' . $limit);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $urlWildcards = $this->persistenceHandler->urlWildcardHandler()->loadAll($offset, $limit);
        $cacheItem->set($urlWildcards);
        $cacheItem->tag($this->getCacheTags($urlWildcards));
        $cacheItem->tag(
            [
                'ez-urlWildcard-all-' . $offset . '-' . $limit,
            ]
        );
        $this->cache->save($cacheItem);

        return $urlWildcards;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler::lookup
     */
    public function lookup($url)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'url' => $url
            ]
        );

        $cacheItem = $this->cache->getItem('ez-urlWildcard-source-', $this->escapeForCacheKey($url));

        if ($cacheItem->isHit()) {
            return $cacheItem;
        }

        $urlWildcard = $this->persistenceHandler->urlWildcardHandler()->lookup($url);
        $cacheItem->set($urlWildcard);
        $cacheItem->tag($this->getCacheTags([$urlWildcard]));
        $this->cache->save($cacheItem);

        $cacheItemById = $this->cache->getItem('ez-urlWildcard-id-' . $urlWildcard->id);
        $cacheItemById->set($urlWildcard);
        $cacheItem->tag($this->getCacheTags([$urlWildcard]));
        $this->cache->save($cacheItemById);

        return $urlWildcard;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard[] $urlWildcards
     * @return array
     */
    private function getCacheTags(array $urlWildcards): array
    {
        $tags = ['ez-urlWildcard-all'];

        foreach ($urlWildcards as $urlWildcard) {
            $tags[] = 'ez-urlWildcard-id-' . $urlWildcard->id;
            $tags[] = 'ez-urlWildcard-source-' . $this->escapeForCacheKey($urlWildcard->sourceUrl);
        }

        return $tags;
    }
}
