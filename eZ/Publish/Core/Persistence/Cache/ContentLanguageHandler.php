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
    private const LANGUAGE_TAG = 'language';
    private const LANGUAGE_CODE_TAG = 'language_code';
    private const LANGUAGE_LIST_TAG = 'language_list';

    /** @var callable */
    private $getTags;

    /** @var callable */
    private $getKeys;

    /**
     * Set callback functions for use in cache retrival.
     */
    protected function init(): void
    {
        $tagGenerator = $this->tagGenerator;

        $this->getTags = static function (Language $language) use ($tagGenerator) {
            return [
                $tagGenerator->generate(self::LANGUAGE_TAG, [$language->id]),
            ];
        };
        $this->getKeys = function (Language $language) use ($tagGenerator) {
            return [
                $tagGenerator->generate(self::LANGUAGE_TAG, [$language->id], true),
                $tagGenerator->generate(
                    self::LANGUAGE_CODE_TAG,
                    [$this->escapeForCacheKey($language->languageCode)],
                    true
                ),
            ];
        };
    }

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $struct]);
        $this->cache->deleteItems([
            $this->tagGenerator->generate(self::LANGUAGE_LIST_TAG, [], true),
        ]);

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
            $this->tagGenerator->generate(self::LANGUAGE_LIST_TAG, [], true),
            $this->tagGenerator->generate(self::LANGUAGE_TAG, [$struct->id], true),
            $this->tagGenerator->generate(
                self::LANGUAGE_CODE_TAG,
                [$this->escapeForCacheKey($struct->languageCode)],
                true
            ),
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
            $this->tagGenerator->generate(self::LANGUAGE_TAG, [], true) . '-',
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
            $this->tagGenerator->generate(self::LANGUAGE_TAG, [], true) . '-',
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
            $this->tagGenerator->generate(self::LANGUAGE_CODE_TAG, [], true) . '-',
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
            $this->tagGenerator->generate(self::LANGUAGE_CODE_TAG, [], true) . '-',
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
            $this->tagGenerator->generate(self::LANGUAGE_LIST_TAG, [], true),
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
        $this->cache->invalidateTags([
            $this->tagGenerator->generate(self::LANGUAGE_TAG, [$id]),
        ]);

        return $return;
    }
}
