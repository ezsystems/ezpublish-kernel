<?php

/**
 * File containing the LanguageHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as ContentLanguageHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\Language\Handler
 */
class ContentLanguageHandler extends AbstractInMemoryHandler implements ContentLanguageHandlerInterface
{
    /** @var callable */
    private $getTags;

    /** @var callable */
    private $getKeys;

    public function __construct(
        TagAwareAdapterInterface $cache,
        PersistenceHandler $persistenceHandler,
        PersistenceLogger $logger,
        InMemoryCache $inMemory
    ) {
        parent::__construct($cache, $persistenceHandler, $logger, $inMemory);

        $this->getTags = static function (Language $language) { return ['language-' . $language->id]; };
        $this->getKeys = static function (Language $language) {
            return [
                'ez-language-' . $language->id,
                'ez-language-code-' . $language->languageCode,
            ];
        };
    }

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));
        $this->deleteCache(['ez-language-list']);

        return $this->persistenceHandler->contentLanguageHandler()->create($struct);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Language $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));
        $return = $this->persistenceHandler->contentLanguageHandler()->update($struct);

        $this->deleteCache([
            'ez-language-list',
            'ez-language-' . $struct->id,
            'ez-language-code-' . $struct->languageCode,
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
            'ez-language-',
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
            'ez-language-',
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
            $languageCode,
            'ez-language-code-',
            function ($languageCode) {
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
            $languageCodes,
            'ez-language-code-',
            function (array $languageCodes) {
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
            'ez-language-list',
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
        $this->logger->logCall(__METHOD__, array('language' => $id));
        $return = $this->persistenceHandler->contentLanguageHandler()->delete($id);

        // As we don't have locale we clear cache by tag invalidation
        $this->invalidateCache(['language-' . $id]);

        return $return;
    }
}
