<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;

use eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache;
use Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGeneratorInterface;
use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as BaseLanguageHandler;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct;

/**
 * Language Handler.
 */
class CachingHandler implements BaseLanguageHandler
{
    private const LANGUAGE_IDENTIFIER = 'language';
    private const LANGUAGE_CODE_IDENTIFIER = 'language_code';
    private const LANGUAGE_LIST_IDENTIFIER = 'language_list';

    /**
     * Inner Language handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected $innerHandler;

    /**
     * Language cache.
     *
     * @var \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache
     */
    protected $cache;

    /** @var \Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGeneratorInterface */
    protected $cacheIdentifierGenerator;

    /**
     * Creates a caching handler around $innerHandler.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $innerHandler
     * @param \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache $cache
     * @param \Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGeneratorInterface $cacheIdentifierGenerator
     */
    public function __construct(
        BaseLanguageHandler $innerHandler,
        InMemoryCache $cache,
        CacheIdentifierGeneratorInterface $cacheIdentifierGenerator
    ) {
        $this->innerHandler = $innerHandler;
        $this->cache = $cache;
        $this->cacheIdentifierGenerator = $cacheIdentifierGenerator;
    }

    /**
     * Create a new language.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\CreateStruct $struct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function create(CreateStruct $struct)
    {
        $language = $this->innerHandler->create($struct);
        $this->storeCache([$language]);

        return $language;
    }

    /**
     * Update language.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $language
     */
    public function update(Language $language)
    {
        $this->innerHandler->update($language);
        $this->storeCache([$language]);
    }

    /**
     * Get language by id.
     *
     * @param mixed $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If language could not be found by $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function load($id)
    {
        $language = $this->cache->get(
            $this->cacheIdentifierGenerator->generateKey(self::LANGUAGE_IDENTIFIER, [$id], true)
        );

        if ($language === null) {
            $language = $this->innerHandler->load($id);
            $this->storeCache([$language]);
        }

        return $language;
    }

    /**
     * {@inheritdoc}
     */
    public function loadList(array $ids): iterable
    {
        $missing = [];
        $languages = [];
        foreach ($ids as $id) {
            if ($language = $this->cache->get($this->cacheIdentifierGenerator->generateKey(self::LANGUAGE_IDENTIFIER, [$id], true))) {
                $languages[$id] = $language;
            } else {
                $missing[] = $id;
            }
        }

        if (!empty($missing)) {
            $loaded = $this->innerHandler->loadList($missing);
            $this->storeCache($loaded);
            /** @noinspection AdditionOperationOnArraysInspection */
            $languages += $loaded;
        }

        return $languages;
    }

    /**
     * Get language by Language Code (eg: eng-GB).
     *
     * @param string $languageCode
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If language could not be found by $languageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function loadByLanguageCode($languageCode)
    {
        $language = $this->cache->get(
            $this->cacheIdentifierGenerator->generateKey(self::LANGUAGE_CODE_IDENTIFIER, [$languageCode], true)
        );

        if ($language === null) {
            $language = $this->innerHandler->loadByLanguageCode($languageCode);
            $this->storeCache([$language]);
        }

        return $language;
    }

    /**
     * {@inheritdoc}
     */
    public function loadListByLanguageCodes(array $languageCodes): iterable
    {
        $missing = [];
        $languages = [];
        foreach ($languageCodes as $languageCode) {
            if ($language = $this->cache->get($this->cacheIdentifierGenerator->generateKey(self::LANGUAGE_CODE_IDENTIFIER, [$languageCode], true))) {
                $languages[$languageCode] = $language;
            } else {
                $missing[] = $languageCode;
            }
        }

        if (!empty($missing)) {
            $loaded = $this->innerHandler->loadListByLanguageCodes($missing);
            $this->storeCache($loaded);
            /** @noinspection AdditionOperationOnArraysInspection */
            $languages += $loaded;
        }

        return $languages;
    }

    /**
     * Get all languages.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    public function loadAll()
    {
        $prefixedLanguageListTag = $this->cacheIdentifierGenerator->generateKey(self::LANGUAGE_LIST_IDENTIFIER, [], true);
        $languages = $this->cache->get($prefixedLanguageListTag);

        if ($languages === null) {
            $languages = $this->innerHandler->loadAll();
            $this->storeCache($languages, $prefixedLanguageListTag);
        }

        return $languages;
    }

    /**
     * Delete a language.
     *
     * @param mixed $id
     */
    public function delete($id)
    {
        $this->innerHandler->delete($id);
        // Delete by primary key will remove the object, so we don't need to clear `ez-language-code-` here.
        $this->cache->deleteMulti([
            $this->cacheIdentifierGenerator->generateKey(self::LANGUAGE_IDENTIFIER, [$id], true),
            $this->cacheIdentifierGenerator->generateKey(self::LANGUAGE_LIST_IDENTIFIER, [], true),
        ]);
    }

    /**
     * Clear internal in-memory cache.
     */
    public function clearCache(): void
    {
        $this->cache->clear();
    }

    /**
     * Helper to store languages in internal in-memory cache with all needed keys.
     *
     * @param array $languages
     * @param string|null $listIndex
     */
    protected function storeCache(array $languages, string $listIndex = null): void
    {
        $generator = $this->cacheIdentifierGenerator;

        $this->cache->setMulti(
            $languages,
            static function (Language $language) use ($generator) {
                return [
                    $generator->generateKey(self::LANGUAGE_IDENTIFIER, [$language->id], true),
                    $generator->generateKey(self::LANGUAGE_CODE_IDENTIFIER, [$language->languageCode], true),
                ];
            },
            $listIndex
        );
    }
}
