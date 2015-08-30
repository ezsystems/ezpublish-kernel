<?php

/**
 * File containing the LanguageHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\Language\Handler as ContentLanguageHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Language\Handler
 */
class ContentLanguageHandler extends AbstractHandler implements ContentLanguageHandlerInterface
{
    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Language\Handler::create
     */
    public function create(CreateStruct $struct)
    {
        $this->logger->startLogCall(__METHOD__, array('struct' => $struct));

        $language = $this->persistenceHandler->contentLanguageHandler()->create($struct);

        $this->logger->lapLogCall(__METHOD__);

        $this->cache->getItem('language', $language->id)->set($language);

        $this->logger->stopLogCall(__METHOD__);

        return $language;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Language\Handler::update
     */
    public function update(Language $struct)
    {
        $this->logger->startLogCall(__METHOD__, array('struct' => $struct));
        $return = $this->persistenceHandler->contentLanguageHandler()->update($struct);

        $this->logger->lapLogCall(__METHOD__);

        $this->cache->clear('language', $struct->id);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Language\Handler::load
     */
    public function load($id)
    {
        $cache = $this->cache->getItem('language', $id);
        $language = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->startLogCall(__METHOD__, array('language' => $id));
            $cache->set($language = $this->persistenceHandler->contentLanguageHandler()->load($id));
            $this->logger->stopLogCall(__METHOD__);
        }

        return $language;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Language\Handler::loadByLanguageCode
     */
    public function loadByLanguageCode($languageCode)
    {
        $this->logger->startLogCall(__METHOD__, array('language' => $languageCode));

        $return = $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode($languageCode);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Language\Handler::loadAll
     */
    public function loadAll()
    {
        $this->logger->startLogCall(__METHOD__);

        $return = $this->persistenceHandler->contentLanguageHandler()->loadAll();

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Language\Handler::delete
     */
    public function delete($id)
    {
        $this->logger->startLogCall(__METHOD__, array('language' => $id));

        $return = $this->persistenceHandler->contentLanguageHandler()->delete($id);

        $this->logger->lapLogCall(__METHOD__);

        $this->cache->clear('language', $id);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }
}
