<?php
/**
 * File containing a abstract Persistence Factory
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence;

use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $persistenceId;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param string $persistenceId
     */
    public function __construct( ContainerInterface $container, $persistenceId )
    {
        $this->container = $container;
        $this->persistenceId = $persistenceId;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Handler
     */
    public function getPersistenceHandler()
    {
        return $this->container->get( $this->persistenceId );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function getContentHandler()
    {
        return $this->getPersistenceHandler()->contentHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    public function getSearchHandler()
    {
        return $this->getPersistenceHandler()->searchHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    public function getContentTypeHandler()
    {
        return $this->getPersistenceHandler()->contentTypeHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    public function getContentLanguageHandler()
    {
        return $this->getPersistenceHandler()->contentLanguageHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function getLocationHandler()
    {
        return $this->getPersistenceHandler()->locationHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    public function getObjectStateHandler()
    {
        return $this->getPersistenceHandler()->objectStateHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function getTrashHandler()
    {
        return $this->getPersistenceHandler()->trashHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\User\Handler
     */
    public function getUserHandler()
    {
        return $this->getPersistenceHandler()->userHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function getSectionHandler()
    {
        return $this->getPersistenceHandler()->sectionHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
     */
    public function getUrlAliasHandler()
    {
        return $this->getPersistenceHandler()->urlAliasHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
     */
    public function getUrlWildcardHandler()
    {
        return $this->getPersistenceHandler()->urlWildcardHandler();
    }
}
