<?php
/**
 * File containing a abstract Persistence Factory
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence;

use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;

/**
 * A reusable factory for all the "storage engine" handlers
 *
 * This class is kept in Core as it is a temporary one until
 * Legacy and InMemory is refactored to provide all handlers as
 * decoupled services.
 *
 * Tests? See Cache\Tests\FactoryTests.php
 */
class Factory
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    public function __construct( PersistenceHandler $persistenceHandler )
    {
        $this->persistenceHandler = $persistenceHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Handler
     */
    public function getPersistenceHandler()
    {
        return $this->persistenceHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function getContentHandler()
    {
        return $this->persistenceHandler->contentHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    public function getSearchHandler()
    {
        return $this->persistenceHandler->searchHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    public function getContentTypeHandler()
    {
        return $this->persistenceHandler->contentTypeHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    public function getContentLanguageHandler()
    {
        return $this->persistenceHandler->contentLanguageHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function getLocationHandler()
    {
        return $this->persistenceHandler->locationHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Search\Handler
     */
    public function getLocationSearchHandler()
    {
        return $this->persistenceHandler->locationSearchHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    public function getObjectStateHandler()
    {
        return $this->persistenceHandler->objectStateHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function getTrashHandler()
    {
        return $this->persistenceHandler->trashHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\User\Handler
     */
    public function getUserHandler()
    {
        return $this->persistenceHandler->userHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function getSectionHandler()
    {
        return $this->persistenceHandler->sectionHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
     */
    public function getUrlAliasHandler()
    {
        return $this->persistenceHandler->urlAliasHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
     */
    public function getUrlWildcardHandler()
    {
        return $this->persistenceHandler->urlWildcardHandler();
    }
}
