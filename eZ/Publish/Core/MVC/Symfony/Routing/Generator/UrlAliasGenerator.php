<?php
/**
 * File containing the UrlAliasGenerator class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Routing\Generator;

use eZ\Publish\Core\MVC\Symfony\Routing\Generator,
    Symfony\Component\Routing\RouterInterface;

/**
 * URL generator for UrlAlias based links
 *
 * @see \eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter
 */
class UrlAliasGenerator extends Generator
{
    const INTERNAL_LOCATION_ROUTE = '_ezpublishLocation';

    private $lazyRepository;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    public function __construct( \Closure $lazyRepository, RouterInterface $router )
    {
        $this->lazyRepository = $lazyRepository;
        $this->router = $router;
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepository()
    {
        $lazyRepository = $this->lazyRepository;
        return $lazyRepository();
    }

    /**
     * Generates the URL from $urlResource and $parameters.
     * Entries in $parameters will be added in the query string.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param array $parameters
     * @return string
     */
    public function doGenerate( $location, array $parameters )
    {
        $urlAliases = $this->getRepository()->getURLAliasService()->listLocationAliases(
            $location,
            false,
            // TODO : Don't hardcode language. Build the Repository with configured prioritized languages instead.
            'eng-GB'
        );

        $queryString = '';
        if ( !empty( $parameters ) )
        {
            $queryString = '?' . http_build_query( $parameters, '', '&' );
        }

        if ( !empty( $urlAliases ) )
            $path = $urlAliases[0]->path;
        else
            $path = $this->router->generate(
                self::INTERNAL_LOCATION_ROUTE,
                array( 'locationId' => $location->id )
            );

        return $path . $queryString;
    }
}
