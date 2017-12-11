<?php

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\SPI\Persistence\URL\Handler as URLHandlerInterface;
use eZ\Publish\SPI\Persistence\URL\URLUpdateStruct;

/**
 * SPI cache for URL Handler.
 *
 * @see \eZ\Publish\SPI\Persistence\URL\Handler
 */
class URLHandler implements URLHandlerInterface
{
    /**
     * @var \eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator
     */
    protected $cache;

    /**
     * @var \eZ\Publish\SPI\Persistence\URL\Handler
     */
    protected $persistenceHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\PersistenceLogger
     */
    protected $logger;

    /**
     * Setups current handler with everything needed.
     *
     * @param \eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator $cache
     * @param \eZ\Publish\SPI\Persistence\URL\Handler $persistenceHandler
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     */
    public function __construct(
        CacheServiceDecorator $cache,
        URLHandlerInterface $persistenceHandler,
        PersistenceLogger $logger)
    {
        $this->cache = $cache;
        $this->persistenceHandler = $persistenceHandler;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function updateUrl($id, URLUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, [
            'url' => $id,
            'struct' => $struct,
        ]);

        $url = $this->persistenceHandler->updateUrl($id, $struct);

        $this->cache->clear('url', $id);
        $this->cache->clear('content');

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

        return $this->persistenceHandler->find($query);
    }

    /**
     * {@inheritdoc}
     */
    public function loadById($id)
    {
        $cache = $this->cache->getItem('url', $id);

        $url = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, ['url' => $id]);
            $url = $this->persistenceHandler->loadById($id);
            $cache->set($url)->save();
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByUrl($url)
    {
        return $this->persistenceHandler->loadByUrl($url);
    }

    /**
     * {@inheritdoc}
     */
    public function findUsages($id)
    {
        $cache = $this->cache->getItem('url', $id, 'usages');

        $usages = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, ['url' => $id]);
            $usages = $this->persistenceHandler->findUsages($id);
            $cache->set($usages)->save();
        }

        return $usages;
    }
}
