<?php
/**
 * File containing the LanguageHandler class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
    public function create( CreateStruct $struct )
    {
        $this->logger->logCall( __METHOD__, array( 'struct' => $struct ) );
        $language = $this->persistenceHandler->contentLanguageHandler()->create( $struct );
        $this->cache->getItem( 'language', $language->id )->set( $language );
        return $language;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Language\Handler::update
     */
    public function update( Language $struct )
    {
        $this->logger->logCall( __METHOD__, array( 'struct' => $struct ) );
        $return = $this->persistenceHandler->contentLanguageHandler()->update( $struct );

        $this->cache->clear( 'language', $struct->id );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Language\Handler::load
     */
    public function load( $id )
    {
        $cache = $this->cache->getItem( 'language', $id );
        $language = $cache->get();
        if ( $cache->isMiss() )
        {
            $this->logger->logCall( __METHOD__, array( 'language' => $id ) );
            $cache->set( $language = $this->persistenceHandler->contentLanguageHandler()->load( $id ) );
        }

        return $language;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Language\Handler::loadByLanguageCode
     */
    public function loadByLanguageCode( $languageCode )
    {
        $this->logger->logCall( __METHOD__, array( 'language' => $languageCode ) );
        return $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode( $languageCode );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Language\Handler::loadAll
     */
    public function loadAll()
    {
        $this->logger->logCall( __METHOD__ );
        return $this->persistenceHandler->contentLanguageHandler()->loadAll();
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Language\Handler::delete
     */
    public function delete( $id )
    {
        $this->logger->logCall( __METHOD__, array( 'language' => $id ) );
        $return = $this->persistenceHandler->contentLanguageHandler()->delete( $id );

        $this->cache->clear( 'language', $id );
        return $return;
    }
}
