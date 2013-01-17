<?php
/**
 * File containing the UrlAlias Handler implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler as UrlAliasHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\UrlAlias;
use eZ\Publish\Core\Persistence\Factory as PersistenceFactory;
use Tedivm\StashBundle\Service\CacheService;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;

/**
 * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
 */
class UrlAliasHandler implements UrlAliasHandlerInterface
{
    /**
     * @var \Tedivm\StashBundle\Service\CacheService
     */
    protected $cache;

    /**
     * @var \eZ\Publish\Core\Persistence\Factory
     */
    protected $persistenceFactory;

    /**
     * @var PersistenceLogger
     */
    protected $logger;

    /**
     * Setups current handler with everything needed
     *
     * @param \Tedivm\StashBundle\Service\CacheService $cache
     * @param \eZ\Publish\Core\Persistence\Factory $persistenceFactory
     * @param PersistenceLogger $logger
     */
    public function __construct(
        CacheService $cache,
        PersistenceFactory $persistenceFactory,
        PersistenceLogger $logger )
    {
        $this->cache = $cache;
        $this->persistenceFactory = $persistenceFactory;
        $this->logger = $logger;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::publishUrlAliasForLocation
     */
    public function publishUrlAliasForLocation( $locationId, $parentLocationId, $name, $languageCode, $alwaysAvailable = false )
    {
        $this->logger->logCall(
            __METHOD__,
            array(
                'location' => $locationId,
                'parent' => $parentLocationId,
                'name' => $name,
                'language' => $languageCode,
                'alwaysAvailable' => $alwaysAvailable
            )
        );

        $this->persistenceFactory->getUrlAliasHandler()->publishUrlAliasForLocation(
            $locationId, $parentLocationId, $name, $languageCode, $alwaysAvailable
        );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::createCustomUrlAlias
     */
    public function createCustomUrlAlias( $locationId, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false )
    {
        $this->logger->logCall(
            __METHOD__,
            array(
                'location' => $locationId,
                '$path' => $path,
                '$forwarding' => $forwarding,
                'language' => $languageCode,
                'alwaysAvailable' => $alwaysAvailable
            )
        );

        return $this->persistenceFactory->getUrlAliasHandler()->createCustomUrlAlias(
            $locationId, $path, $forwarding, $languageCode, $alwaysAvailable
        );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::createGlobalUrlAlias
     */
    public function createGlobalUrlAlias( $resource, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false )
    {
        $this->logger->logCall(
            __METHOD__,
            array(
                'resource' => $resource,
                'path' => $path,
                'forwarding' => $forwarding,
                'language' => $languageCode,
                'alwaysAvailable' => $alwaysAvailable
            )
        );

        return $this->persistenceFactory->getUrlAliasHandler()->createGlobalUrlAlias(
            $resource, $path, $forwarding, $languageCode, $alwaysAvailable
        );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::listGlobalURLAliases
     */
    public function listGlobalURLAliases( $languageCode = null, $offset = 0, $limit = -1 )
    {
        $this->logger->logCall( __METHOD__, array( 'language' => $languageCode, 'offset' => $offset, 'limit' => $limit ) );
        return $this->persistenceFactory->getUrlAliasHandler()->listGlobalURLAliases( $languageCode, $offset, $limit );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::listURLAliasesForLocation
     */
    public function listURLAliasesForLocation( $locationId, $custom = false )
    {
        $this->logger->logCall( __METHOD__, array( 'location' => $locationId, 'custom' => $custom ) );
        return $this->persistenceFactory->getUrlAliasHandler()->listURLAliasesForLocation( $locationId, $custom );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::removeURLAliases
     */
    public function removeURLAliases( array $urlAliases )
    {
        $this->logger->logCall( __METHOD__, array( 'aliases' => $urlAliases ) );
        return $this->persistenceFactory->getUrlAliasHandler()->removeURLAliases( $urlAliases );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::lookup
     */
    public function lookup( $url )
    {
        $this->logger->logCall( __METHOD__, array( 'url' => $url ) );
        return $this->persistenceFactory->getUrlAliasHandler()->lookup( $url );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::loadUrlAlias
     */
    public function loadUrlAlias( $id )
    {
        $this->logger->logCall( __METHOD__, array( 'alias' => $id ) );
        return $this->persistenceFactory->getUrlAliasHandler()->loadUrlAlias( $id );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::locationMoved
     */
    public function locationMoved( $locationId, $oldParentId, $newParentId )
    {
        $this->logger->logCall(
            __METHOD__,
            array(
                'location' => $locationId,
                'oldParent' => $oldParentId,
                'newParent' => $newParentId
            )
        );

        return $this->persistenceFactory->getUrlAliasHandler()->locationMoved( $locationId, $oldParentId, $newParentId );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::locationCopied
     */
    public function locationCopied( $locationId, $oldParentId, $newParentId )
    {
        $this->logger->logCall(
            __METHOD__,
            array(
                'location' => $locationId,
                'oldParent' => $oldParentId,
                'newParent' => $newParentId
            )
        );

        return $this->persistenceFactory->getUrlAliasHandler()->locationCopied( $locationId, $oldParentId, $newParentId );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::locationDeleted
     */
    public function locationDeleted( $locationId )
    {
        $this->logger->logCall( __METHOD__, array( 'location' => $locationId ) );
        return $this->persistenceFactory->getUrlAliasHandler()->locationDeleted( $locationId );
    }
}
