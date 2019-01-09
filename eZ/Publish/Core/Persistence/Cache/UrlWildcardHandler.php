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
        $this->cache->clear('urlWildcard-all');

        $this->cache->getItem('urlWildcard/id', $urlWildcard->id)->set($urlWildcard)->save();
        $this->cache->getItem('urlWildcard/source', $urlWildcard->sourceUrl)->set($urlWildcard)->save();

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
        $this->cache->clear('urlWildcard-all');

        $this->cache->clear('urlWildcard/id', $urlWildcard->id);
        $this->cache->clear('urlWildcard/source', $urlWildcard->sourceUrl);
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

        $cache = $this->cache->getItem('urlWildcard/id', $id);
        $urlWildcard = $cache->get();
        if ($cache->isMiss()) {
            $urlWildcard = $this->persistenceHandler->urlWildcardHandler()->load($id);
            $cache->set($urlWildcard)->save();
            $this->cache->getItem('urlWildcard/source', $urlWildcard->sourceUrl)->set($urlWildcard)->save();
        }

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

        $cache = $this->cache->getItem('urlWildcard-all', $offset, $limit);
        $urlWildcards = $cache->get();
        if ($cache->isMiss()) {
            $urlWildcards = $this->persistenceHandler->urlWildcardHandler()->loadAll($offset, $limit);
            $cache->set($urlWildcards)->save();
        }

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

        $cache = $this->cache->getItem('urlWildcard/source', $url);
        $urlWildcard = $cache->get();
        if ($cache->isMiss()) {
            $urlWildcard = $this->persistenceHandler->urlWildcardHandler()->lookup($url);
            $cache->set($urlWildcard)->save();
            $this->cache->getItem('urlWildcard/id', $urlWildcard->id)->set($urlWildcard)->save();
        }

        return $urlWildcard;
    }
}