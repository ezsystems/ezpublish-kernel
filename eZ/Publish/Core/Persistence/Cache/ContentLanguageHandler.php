<?php

/**
 * File containing the LanguageHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\Language\Handler as ContentLanguageHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\Language\Handler
 */
class ContentLanguageHandler extends AbstractHandler implements ContentLanguageHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));

        return $this->persistenceHandler->contentLanguageHandler()->create($struct);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Language $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));
        $return = $this->persistenceHandler->contentLanguageHandler()->update($struct);

        $this->cache->invalidateTags(['language-' . $struct->id]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        $cacheItem = $this->cache->getItem('ez-language-' . $id);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('language' => $id));
        $language = $this->persistenceHandler->contentLanguageHandler()->load($id);

        $cacheItem->set($language);
        $cacheItem->tag('language-' . $language->id);
        $this->cache->save($cacheItem);

        return $language;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByLanguageCode($languageCode)
    {
        $cacheItem = $this->cache->getItem('ez-language-' . $languageCode . '-by-code');
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('language' => $languageCode));
        $language = $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode($languageCode);

        $cacheItem->set($language);
        $cacheItem->tag('language-' . $language->id);
        $this->cache->save($cacheItem);

        return $language;
    }

    /**
     * {@inheritdoc}
     */
    public function loadAll()
    {
        $this->logger->logCall(__METHOD__);

        return $this->persistenceHandler->contentLanguageHandler()->loadAll();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $this->logger->logCall(__METHOD__, array('language' => $id));
        $return = $this->persistenceHandler->contentLanguageHandler()->delete($id);

        $this->cache->invalidateTags(['language-' . $id]);

        return $return;
    }
}
