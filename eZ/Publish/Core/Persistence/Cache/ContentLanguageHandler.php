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
        $language = $this->persistenceHandler->contentLanguageHandler()->create($struct);
        $this->cache->getItem('language', $language->id)->set($language)->save();

        return $language;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Language $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));
        $return = $this->persistenceHandler->contentLanguageHandler()->update($struct);

        $this->cache->clear('language', $struct->id);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        $cache = $this->cache->getItem('language', $id);
        $language = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('language' => $id));
            $cache->set($language = $this->persistenceHandler->contentLanguageHandler()->load($id))->save();
        }

        return $language;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByLanguageCode($languageCode)
    {
        $this->logger->logCall(__METHOD__, array('language' => $languageCode));

        return $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode($languageCode);
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

        $this->cache->clear('language', $id);

        return $return;
    }
}
