<?php

/**
 * File containing the UrlAlias Handler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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
     * Constant used for storing not found results for lookup().
     */
    const NOT_FOUND = 0;

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::publishUrlAliasForLocation
     */
    public function publishUrlAliasForLocation(
        $locationId,
        $parentLocationId,
        $name,
        $languageCode,
        $alwaysAvailable = false,
        $updatePathIdentificationString = false
    ) {
        $this->logger->logCall(
            __METHOD__,
            array(
                'location' => $locationId,
                'parent' => $parentLocationId,
                'name' => $name,
                'language' => $languageCode,
                'alwaysAvailable' => $alwaysAvailable,
            )
        );
        $this->clearLocation($locationId);

        $this->persistenceHandler->urlAliasHandler()->publishUrlAliasForLocation(
            $locationId,
            $parentLocationId,
            $name,
            $languageCode,
            $alwaysAvailable,
            $updatePathIdentificationString
        );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::createCustomUrlAlias
     */
    public function createCustomUrlAlias($locationId, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false)
    {
        $this->logger->logCall(
            __METHOD__,
            array(
                'location' => $locationId,
                '$path' => $path,
                '$forwarding' => $forwarding,
                'language' => $languageCode,
                'alwaysAvailable' => $alwaysAvailable,
            )
        );

        $urlAlias = $this->persistenceHandler->urlAliasHandler()->createCustomUrlAlias(
            $locationId,
            $path,
            $forwarding,
            $languageCode,
            $alwaysAvailable
        );

        $this->cache->getItem('urlAlias', $urlAlias->id)->set($urlAlias)->save();
        $cache = $this->cache->getItem('urlAlias', 'location', $urlAlias->destination, 'custom');
        $urlAliasIds = $cache->get();
        if ($cache->isMiss()) {
            $urlAliasIds = array();
        }

        $urlAliasIds[] = $urlAlias->id;
        $cache->set($urlAliasIds)->save();

        return $urlAlias;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::createGlobalUrlAlias
     */
    public function createGlobalUrlAlias($resource, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false)
    {
        $this->logger->logCall(
            __METHOD__,
            array(
                'resource' => $resource,
                'path' => $path,
                'forwarding' => $forwarding,
                'language' => $languageCode,
                'alwaysAvailable' => $alwaysAvailable,
            )
        );

        $urlAlias = $this->persistenceHandler->urlAliasHandler()->createGlobalUrlAlias(
            $resource,
            $path,
            $forwarding,
            $languageCode,
            $alwaysAvailable
        );

        $this->cache->getItem('urlAlias', $urlAlias->id)->set($urlAlias)->save();

        return $urlAlias;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::listGlobalURLAliases
     */
    public function listGlobalURLAliases($languageCode = null, $offset = 0, $limit = -1)
    {
        $this->logger->logCall(__METHOD__, array('language' => $languageCode, 'offset' => $offset, 'limit' => $limit));

        return $this->persistenceHandler->urlAliasHandler()->listGlobalURLAliases($languageCode, $offset, $limit);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::listURLAliasesForLocation
     */
    public function listURLAliasesForLocation($locationId, $custom = false)
    {
        // Look for location to list of url alias id's cache
        if ($custom) {
            $cache = $this->cache->getItem('urlAlias', 'location', $locationId, 'custom');
        } else {
            $cache = $this->cache->getItem('urlAlias', 'location', $locationId);
        }
        $urlAliasIds = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('location' => $locationId, 'custom' => $custom));
            $urlAliases = $this->persistenceHandler->urlAliasHandler()->listURLAliasesForLocation($locationId, $custom);

            $urlAliasIds = array();
            foreach ($urlAliases as $urlAlias) {
                $urlAliasIds[] = $urlAlias->id;
            }

            $cache->set($urlAliasIds)->save();
        } else {
            // Reuse loadUrlAlias for the url alias object cache
            $urlAliases = array();
            foreach ($urlAliasIds as $urlAliasId) {
                $urlAliases[] = $this->loadUrlAlias($urlAliasId);
            }
        }

        return $urlAliases;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::removeURLAliases
     */
    public function removeURLAliases(array $urlAliases)
    {
        $this->logger->logCall(__METHOD__, array('aliases' => $urlAliases));
        $return = $this->persistenceHandler->urlAliasHandler()->removeURLAliases($urlAliases);

        $this->cache->clear('urlAlias', 'url');//TIMBER! (no easy way to do reverse lookup of urls)
        foreach ($urlAliases as $urlAlias) {
            $this->cache->clear('urlAlias', $urlAlias->id);
            if ($urlAlias->type === UrlAlias::LOCATION) {
                $this->cache->clear('urlAlias', 'location', $urlAlias->destination);
            }
            if ($urlAlias->isCustom) {
                $this->cache->clear('urlAlias', 'location', $urlAlias->destination, 'custom');
            }
        }

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::lookup
     */
    public function lookup($url)
    {
        // Look for url to url alias id cache
        // Replace slashes by "|" to be sure not to mix cache key combinations in underlying lib.
        $cacheKey = $url ?: '/';
        $cache = $this->cache->getItem('urlAlias', 'url', $cacheKey);
        $urlAliasId = $cache->get();
        if ($cache->isMiss()) {
            // Also cache "not found" as this function is heavliy used and hance should be cached
            try {
                $this->logger->logCall(__METHOD__, array('url' => $url));
                $urlAlias = $this->persistenceHandler->urlAliasHandler()->lookup($url);
                $cache->set($urlAlias->id)->save();

                $urlAliasCache = $this->cache->getItem('urlAlias', $urlAlias->id);
                $urlAliasCache->set($urlAlias)->save();
            } catch (APINotFoundException $e) {
                $cache->set(self::NOT_FOUND)->save();
                throw $e;
            }
        } elseif ($urlAliasId === self::NOT_FOUND) {
            throw new NotFoundException('UrlAlias', $url);
        } else {
            $urlAlias = $this->loadUrlAlias($urlAliasId);
        }

        return $urlAlias;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::loadUrlAlias
     */
    public function loadUrlAlias($id)
    {
        // Look for url alias cache
        $cache = $this->cache->getItem('urlAlias', $id);
        $urlAlias = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('alias' => $id));
            $urlAlias = $this->persistenceHandler->urlAliasHandler()->loadUrlAlias($id);
            $cache->set($urlAlias)->save();
        }

        return $urlAlias;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::locationMoved
     */
    public function locationMoved($locationId, $oldParentId, $newParentId)
    {
        $this->logger->logCall(
            __METHOD__,
            array(
                'location' => $locationId,
                'oldParent' => $oldParentId,
                'newParent' => $newParentId,
            )
        );

        $return = $this->persistenceHandler->urlAliasHandler()->locationMoved($locationId, $oldParentId, $newParentId);
        $this->cache->clear('urlAlias', 'url');//TIMBER! (Will have to load url aliases for location to be able to clear specific entries)

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::locationCopied
     */
    public function locationCopied($locationId, $newLocationId, $newParentId)
    {
        $this->logger->logCall(
            __METHOD__,
            array(
                'oldLocation' => $locationId,
                'newLocation' => $newLocationId,
                'newParent' => $newParentId,
            )
        );

        $return = $this->persistenceHandler->urlAliasHandler()->locationCopied(
            $locationId,
            $newLocationId,
            $newParentId
        );
        $this->cache->clear('urlAlias', 'url'); // required due to caching not found aliases

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler::locationDeleted
     */
    public function locationDeleted($locationId)
    {
        $this->logger->logCall(__METHOD__, array('location' => $locationId));
        $return = $this->persistenceHandler->urlAliasHandler()->locationDeleted($locationId);

        $this->clearLocation($locationId);

        return $return;
    }

    /**
     * @param $locationId
     */
    protected function clearLocation($locationId)
    {
        $locationCache = $this->cache->getItem('urlAlias', 'location', $locationId);

        if ($locationCache->isMiss()) {
            // we need to clear all if we don't have location id in cache
            $this->cache->clear('urlAlias');
        } else {
            $urlAliasIds = $locationCache->get();
            foreach ((array) $urlAliasIds as $urlAliasId) {
                $this->cache->clear('urlAlias', $urlAliasId);
            }
            $this->cache->clear('urlAlias', 'url');
            $locationCache->clear();
        }
    }
}
