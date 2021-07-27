<?php

/**
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
class ContentLanguageHandler extends AbstractInMemoryPersistenceHandler implements ContentLanguageHandlerInterface
{
    /** @var callable */
    private $getTags;

    /** @var callable */
    private $getKeys;

    /**
     * Set callback functions for use in cache retrival.
     */
    protected function init(): void
    {
        $this->getTags = static function (Language $language) {
            return [TagIdentifiers::LANGUAGE . '-' . $language->id];
        };
        $this->getKeys = function (Language $language) {
            return [
                TagIdentifiers::PREFIX . TagIdentifiers::LANGUAGE . '-' . $language->id,
                TagIdentifiers::PREFIX . TagIdentifiers::LANGUAGE_CODE . '-' . $this->escapeForCacheKey($language->languageCode),
            ];
        };
    }

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $struct]);
        $this->cache->deleteItems([TagIdentifiers::PREFIX . TagIdentifiers::LANGUAGE_LIST]);

        return $this->persistenceHandler->contentLanguageHandler()->create($struct);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Language $struct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $struct]);
        $return = $this->persistenceHandler->contentLanguageHandler()->update($struct);

        $this->cache->deleteItems([
            TagIdentifiers::PREFIX . TagIdentifiers::LANGUAGE_LIST,
            TagIdentifiers::PREFIX . TagIdentifiers::LANGUAGE . '-' . $struct->id,
            TagIdentifiers::PREFIX . TagIdentifiers::LANGUAGE_CODE . '-' . $this->escapeForCacheKey($struct->languageCode),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        return $this->getCacheValue(
            $id,
            TagIdentifiers::PREFIX . TagIdentifiers::LANGUAGE . '-',
            function ($id) {
                return $this->persistenceHandler->contentLanguageHandler()->load($id);
            },
            $this->getTags,
            $this->getKeys
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadList(array $ids): iterable
    {
        return $this->getMultipleCacheValues(
            $ids,
            TagIdentifiers::PREFIX . TagIdentifiers::LANGUAGE . '-',
            function (array $ids) {
                return $this->persistenceHandler->contentLanguageHandler()->loadList($ids);
            },
            $this->getTags,
            $this->getKeys
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByLanguageCode($languageCode)
    {
        return $this->getCacheValue(
            $this->escapeForCacheKey($languageCode),
            TagIdentifiers::PREFIX . TagIdentifiers::LANGUAGE_CODE . '-',
            function () use ($languageCode) {
                return $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode($languageCode);
            },
            $this->getTags,
            $this->getKeys
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadListByLanguageCodes(array $languageCodes): iterable
    {
        return $this->getMultipleCacheValues(
            array_map([$this, 'escapeForCacheKey'], $languageCodes),
            TagIdentifiers::PREFIX . TagIdentifiers::LANGUAGE_CODE . '-',
            function () use ($languageCodes) {
                return $this->persistenceHandler->contentLanguageHandler()->loadListByLanguageCodes($languageCodes);
            },
            $this->getTags,
            $this->getKeys
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadAll()
    {
        return $this->getListCacheValue(
            TagIdentifiers::PREFIX . TagIdentifiers::LANGUAGE_LIST,
            function () {
                return $this->persistenceHandler->contentLanguageHandler()->loadAll();
            },
            $this->getTags,
            $this->getKeys
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $this->logger->logCall(__METHOD__, ['language' => $id]);
        $return = $this->persistenceHandler->contentLanguageHandler()->delete($id);

        // As we don't have locale we clear cache by tag invalidation
        $this->cache->invalidateTags([TagIdentifiers::LANGUAGE . '-' . $id]);

        return $return;
    }
}
