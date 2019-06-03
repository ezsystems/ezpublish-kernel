<?php

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler as UrlWildcardHandlerInterface;

class UrlWildcardHandler extends AbstractHandler implements UrlWildcardHandlerInterface
{
    /**
     * Constant used for storing not found results for lookup().
     */
    const NOT_FOUND = 0;

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

        $this->cache->invalidateTags(['urlWildcard-notFound']);

        return $urlWildcard;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler::remove
     */
    public function remove($id)
    {
        $this->logger->logCall(__METHOD__, ['id' => $id]);

        $urlWildcard = $this->load($id);

        $this->persistenceHandler->urlWildcardHandler()->remove($urlWildcard->id);

        // need to clear lists of UrlWildcards cached due to loadAll()
        $this->cache->invalidateTags(['ez-urlWildcard-id-' . $urlWildcard->id]);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler::load
     */
    public function load($id)
    {
        $this->logger->logCall(__METHOD__, ['id' => $id]);

        $cacheItem = $this->cache->getItem('ez-urlWildcard-id-' . $id);

        if ($cacheItem->isHit()) {
            if (($return = $cacheItem->get()) === self::NOT_FOUND) {
                throw new NotFoundException('UrlWildcard', $id);
            }

            return $return;
        }

        $urlWildcard = $this->persistenceHandler->urlWildcardHandler()->load($id);

        $cacheItem->set($urlWildcard);
        $cacheItem->tag($this->getCacheTags([$urlWildcard]));
        $this->cache->save($cacheItem);

        return $urlWildcard;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler::loadAll
     */
    public function loadAll($offset = 0, $limit = -1)
    {
        $this->logger->logCall(__METHOD__, ['offset' => $offset, 'limit' => $limit]);

        return $this->persistenceHandler->urlWildcardHandler()->loadAll($offset, $limit);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler::lookup
     */
    public function translate(string $sourceUrl): UrlWildcard
    {
        $this->logger->logCall(__METHOD__, ['url' => $sourceUrl]);

        $cacheItem = $this->cache->getItem('ez-urlWildcard-source-' . $this->escapeForCacheKey($sourceUrl));

        if ($cacheItem->isHit()) {
            if (($return = $cacheItem->get()) === self::NOT_FOUND) {
                throw new NotFoundException('UrlWildcard', $sourceUrl);
            }

            return $return;
        }

        $this->logger->logCacheMiss(['url' => $sourceUrl]);
        try {
            $urlWildcard = $this->persistenceHandler->urlWildcardHandler()->translate($sourceUrl);
        } catch (APINotFoundException $e) {
            $cacheItem->set(self::NOT_FOUND)
                ->expiresAfter(30)
                ->tag(['urlWildcard-notFound']);
            $this->cache->save($cacheItem);
            throw $e;
        }

        $cacheItem->set($urlWildcard);
        $cacheItem->tag($this->getCacheTags([$urlWildcard]));
        $this->cache->save($cacheItem);

        return $urlWildcard;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard[] $urlWildcards
     * @return array
     */
    private function getCacheTags(array $urlWildcards): array
    {
        $tags = [];

        foreach ($urlWildcards as $urlWildcard) {
            $tags[] = 'ez-urlWildcard-id-' . $urlWildcard->id;
            $tags[] = 'ez-urlWildcard-source-' . $this->escapeForCacheKey($urlWildcard->sourceUrl);
        }

        return $tags;
    }

    /**
     * @param string $sourceUrl
     * @return bool
     */
    public function exists(string $sourceUrl): bool
    {
        return $this->persistenceHandler->urlWildcardHandler()->exists($sourceUrl);
    }
}
