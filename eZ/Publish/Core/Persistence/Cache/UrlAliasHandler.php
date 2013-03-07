<?php
/**
 * File containing the UrlAlias Handler implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler as UrlAliasHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\UrlAlias;

/**
 * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
 */
class UrlAliasHandler extends AbstractHandler implements UrlAliasHandlerInterface
{
    /**
     * Constant used for storing not found results for lookup()
     */
    const NOT_FOUND = 0;

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

        $this->cache->clear( 'urlAlias', 'location', $locationId  );

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

        $urlAlias = $this->persistenceFactory->getUrlAliasHandler()->createCustomUrlAlias(
            $locationId, $path, $forwarding, $languageCode, $alwaysAvailable
        );

        $this->cache->getItem( 'urlAlias', $urlAlias->id )->set( $urlAlias );
        $this->cache->clear( 'urlAlias', 'location', $urlAlias->destination, 'custom' );
        return $urlAlias;
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

        $urlAlias = $this->persistenceFactory->getUrlAliasHandler()->createGlobalUrlAlias(
            $resource, $path, $forwarding, $languageCode, $alwaysAvailable
        );

        $this->cache->getItem( 'urlAlias', $urlAlias->id )->set( $urlAlias );
        return $urlAlias;
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
        // Look for location to list of url alias id's cache
        $cache = $this->cache->getItem( 'urlAlias', 'location', $locationId . ( $custom ? '/custom' : '' ) );
        $urlAliasIds = $cache->get();
        if ( $cache->isMiss() )
        {
            $this->logger->logCall( __METHOD__, array( 'location' => $locationId, 'custom' => $custom ) );
            $urlAliases = $this->persistenceFactory->getUrlAliasHandler()->listURLAliasesForLocation( $locationId, $custom );

            $urlAliasIds = array();
            foreach ( $urlAliases as $urlAlias )
                $urlAliasIds[] = $urlAlias->id;

            $cache->set( $urlAliasIds );
        }
        else
        {
            // Reuse loadUrlAlias for the url alias object cache
            $urlAliases = array();
            foreach ( $urlAliasIds as $urlAliasId )
                $urlAliases[] = $this->loadUrlAlias( $urlAliasId );
        }

        return $urlAliases;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::removeURLAliases
     */
    public function removeURLAliases( array $urlAliases )
    {
        $this->logger->logCall( __METHOD__, array( 'aliases' => $urlAliases ) );
        $return = $this->persistenceFactory->getUrlAliasHandler()->removeURLAliases( $urlAliases );

        $this->cache->clear( 'urlAlias', 'url' );//TIMBER! (no easy way to do reverse lookup of urls)
        foreach ( $urlAliases as $urlAlias )
        {
            $this->cache->clear( 'urlAlias', $urlAlias->id );
            if ( $urlAlias->type === URLAlias::LOCATION )
                $this->cache->clear( 'urlAlias', 'location', $urlAlias->destination );
        }

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::lookup
     */
    public function lookup( $url )
    {
        // Look for url to url alias id cache
        $cache = $this->cache->getItem( 'urlAlias', 'url', $url );
        $urlAliasId = $cache->get();
        if ( $cache->isMiss() )
        {
            // Also cache "not found" as this function is heavliy used and hance should be cached
            try
            {
                $this->logger->logCall( __METHOD__, array( 'url' => $url ) );
                $urlAlias = $this->persistenceFactory->getUrlAliasHandler()->lookup( $url );
                $cache->set( $urlAlias->id );
            }
            catch ( APINotFoundException $e )
            {
                $cache->set( self::NOT_FOUND );
                throw $e;
            }
        }
        else if ( $urlAliasId === self::NOT_FOUND )
        {
            throw new NotFoundException( 'UrlAlias', $url );
        }
        else
        {
            $urlAlias = $this->loadUrlAlias( $urlAliasId );
        }

        return $urlAlias;

    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::loadUrlAlias
     */
    public function loadUrlAlias( $id )
    {
        // Look for url alias cache
        $cache = $this->cache->getItem( 'urlAlias', $id );
        $urlAlias = $cache->get();
        if ( $cache->isMiss() )
        {
            $this->logger->logCall( __METHOD__, array( 'alias' => $id ) );
            $urlAlias = $this->persistenceFactory->getUrlAliasHandler()->loadUrlAlias( $id );
            $cache->set( $urlAlias );
        }

        return $urlAlias;
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

        $return = $this->persistenceFactory->getUrlAliasHandler()->locationMoved( $locationId, $oldParentId, $newParentId );

        $this->cache->clear( 'urlAlias' );//TIMBER! (Will have to load url aliases for location to be able to clear specific entries)
        //$this->cache->clear( 'urlAlias', 'location', $locationId );
        return $return;
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
        $return = $this->persistenceFactory->getUrlAliasHandler()->locationDeleted( $locationId );

        $this->cache->clear( 'urlAlias', 'location', $locationId );
        return $return;
    }
}
