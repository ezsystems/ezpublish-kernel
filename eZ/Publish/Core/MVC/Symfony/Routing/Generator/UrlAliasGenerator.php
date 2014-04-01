<?php
/**
 * File containing the UrlAliasGenerator class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Routing\Generator;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator;
use Symfony\Component\Routing\RouterInterface;

/**
 * URL generator for UrlAlias based links
 *
 * @see \eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter
 */
class UrlAliasGenerator extends Generator
{
    const INTERNAL_LOCATION_ROUTE = '_ezpublishLocation';

    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    private $repository;

    /**
     * The default router (that works with declared routes).
     *
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $defaultRouter;

    /**
     * @var int
     */
    private $rootLocationId;

    /**
     * @var array
     */
    private $excludedUriPrefixes = array();

    /**
     * @var array
     */
    private $pathPrefixMap = array();

    public function __construct( Repository $repository, RouterInterface $defaultRouter )
    {
        $this->repository = $repository;
        $this->defaultRouter = $defaultRouter;
    }

    /**
     * Generates the URL from $urlResource and $parameters.
     * Entries in $parameters will be added in the query string.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param array $parameters
     *
     * @return string
     */
    public function doGenerate( $location, array $parameters )
    {
        $urlAliases = $this->repository->getURLAliasService()->listLocationAliases( $location, false );

        $queryString = '';
        if ( !empty( $parameters ) )
        {
            $queryString = '?' . http_build_query( $parameters, '', '&' );
        }

        if ( !empty( $urlAliases ) )
        {
            $path = $urlAliases[0]->path;
            // Remove rootLocation's prefix if needed.
            if ( $this->rootLocationId !== null )
            {
                $pathPrefix = $this->getPathPrefixByRootLocationId( $this->rootLocationId );
                // "/" cannot be considered as a path prefix since it's root, so we ignore it.
                if ( $pathPrefix !== '/' && mb_stripos( $path, $pathPrefix ) === 0 )
                {
                    $path = mb_substr( $path, mb_strlen( $pathPrefix ) );
                }
                // Location path is outside configured content tree and doesn't have an excluded prefix.
                // This is most likely an error (from content edition or link generation logic).
                else if ( $pathPrefix !== '/' && !$this->isUriPrefixExcluded( $path ) && $this->logger !== null )
                {
                    $this->logger->warning( "Generating a link to a location outside root content tree: '$path' is outside tree starting to location #$this->rootLocationId" );
                }
            }
        }
        else
        {
            $path = $this->defaultRouter->generate(
                self::INTERNAL_LOCATION_ROUTE,
                array( 'locationId' => $location->id )
            );
        }

        $path = $path ?: '/';
        return $path . $queryString;
    }

    /**
     * Injects current root locationId that will be used for link generation.
     *
     * @param int $rootLocationId
     */
    public function setRootLocationId( $rootLocationId )
    {
        $this->rootLocationId = $rootLocationId;
    }

    /**
     * @param array $excludedUriPrefixes
     */
    public function setExcludedUriPrefixes( array $excludedUriPrefixes )
    {
        $this->excludedUriPrefixes = $excludedUriPrefixes;
    }

    /**
     * Returns path corresponding to $rootLocationId.
     *
     * @param int $rootLocationId
     * @return string
     */
    public function getPathPrefixByRootLocationId( $rootLocationId )
    {
        if ( isset( $this->pathPrefixMap[$rootLocationId] ) )
        {
            return $this->pathPrefixMap[$rootLocationId];
        }

        $this->pathPrefixMap[$rootLocationId] = $this->repository
            ->getURLAliasService()
            ->reverseLookup( $this->loadLocation( $rootLocationId ) )
            ->path;

        return $this->pathPrefixMap[$rootLocationId];
    }

    /**
     * Checks if passed URI has an excluded prefix, when a root location is defined.
     *
     * @param string $uri
     * @return bool
     */
    public function isUriPrefixExcluded( $uri )
    {
        foreach ( $this->excludedUriPrefixes as $excludedPrefix )
        {
            $excludedPrefix = '/' . trim( $excludedPrefix, '/' );
            if ( mb_stripos( $uri, $excludedPrefix ) === 0 )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Loads a location by its locationId, regardless to user limitations since the router is invoked BEFORE security (no user authenticated yet).
     * Not to be used for link generation.
     *
     * @param int $locationId
     * @return \eZ\Publish\Core\Repository\Values\Content\Location
     */
    public function loadLocation( $locationId )
    {
        return $this->repository->sudo(
            function ( $repository ) use ( $locationId )
            {
                /** @var $repository \eZ\Publish\Core\Repository\Repository */
                return $repository->getLocationService()->loadLocation( $locationId );
            }
        );
    }
}
