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
        $this->cache->deleteItem('ez-language-list');

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
    public function loadList(array $ids): iterable
    {
        return $this->getMultipleCacheItems(
            $ids,
            'ez-language-',
            function (array $cacheMissIds) {
                $this->logger->logCall(__CLASS__ . '::loadList', ['languages' => $cacheMissIds]);

                return $this->persistenceHandler->contentLanguageHandler()->loadList($cacheMissIds);
            },
            function (Language $language) {
                return ['language-' . $language->id];
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByLanguageCode($languageCode)
    {
        $cacheItem = $this->cache->getItem('ez-language-code-' . $languageCode);
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
    public function loadListByLanguageCodes(array $languageCodes): iterable
    {
        return $this->getMultipleCacheItems(
            $languageCodes,
            'ez-language-code-',
            function (array $cacheMissIds) {
                $this->logger->logCall(__CLASS__ . '::loadListByLanguageCodes', ['languages' => $cacheMissIds]);

                return $this->persistenceHandler->contentLanguageHandler()->loadListByLanguageCodes($cacheMissIds);
            },
            function (Language $language) {
                return ['language-' . $language->id];
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadAll()
    {
        $cacheItem = $this->cache->getItem('ez-language-list');
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__);
        $languages = $this->persistenceHandler->contentLanguageHandler()->loadAll();

        $cacheTags = [];
        foreach ($languages as $language) {
            $cacheTags[] = 'language-' . $language->id;
        }

        $cacheItem->set($languages);
        $cacheItem->tag($cacheTags);
        $this->cache->save($cacheItem);

        return $languages;
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
