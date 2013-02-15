<?php
/**
 * File containing the GlobalHelper class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating;

use Symfony\Component\DependencyInjection\ContainerInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter;

/**
 * Templating helper object globally accessible, through the "ezpublish" variable (in Twig).
 * Container is injected to be sure to lazy load underlying services and to avoid scope conflict.
 */
class GlobalHelper
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;
    }

    /**
     * Returns the current siteaccess.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess|null
     */
    public function getSiteaccess()
    {
        if ( $this->container->has( 'ezpublish.siteaccess' ) )
        {
            return $this->container->get( 'ezpublish.siteaccess' );
        }
    }

    /**
     * Returns the view parameters as a hash.
     *
     * @return array|null
     */
    public function getViewParameters()
    {
        if ( $this->container->has( 'request' ) )
        {
            return $this->container->get( 'request' )->attributes->get( 'viewParameters' );
        }
    }

    /**
     * Returns the view parameters as a string.
     * e.g. /(foo)/bar
     *
     * @return string
     */
    public function getViewParametersString()
    {
        if ( $this->container->has( 'request' ) )
        {
            return $this->container->get( 'request' )->attributes->get( 'viewParametersString' );
        }
    }

    /**
     * Returns the requested URI string (aka semanticPathInfo).
     *
     * @return string
     */
    public function getRequestedUriString()
    {
        if ( $this->container->has( 'request' ) )
        {
            return $this->container->get( 'request' )->attributes->get( 'semanticPathinfo' );
        }
    }

    /**
     * Returns the "system" URI string.
     * System URI is the URI for internal content controller.
     * E.g. /content/location/123/full
     *
     * If current route is not an URLAlias, then the current Pathinfo is returned.
     *
     * @return null|string
     */
    public function getSystemUriString()
    {
        if ( $this->container->has( 'request' ) )
        {
            /** @var $request \Symfony\Component\HttpFoundation\Request */
            $request = $this->container->get( 'request' );
            if ( $request->attributes->get( '_route' ) === UrlAliasRouter::URL_ALIAS_ROUTE_NAME )
            {
                return $this->container->get( 'router' )
                    ->generate(
                        '_ezpublishLocation',
                        array(
                            'locationId' => $request->attributes->get( 'locationId' ),
                            'viewType' => $request->attributes->get( 'viewType' )
                        )
                    );
            }

            return $this->getRequestedUriString();
        }
    }

    /**
     * Returns the config resolver.
     *
     * @return \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    public function getConfigResolver()
    {
        if ( $this->container->has( 'ezpublish.config.resolver' ) )
        {
            return $this->container->get( 'ezpublish.config.resolver' );
        }
    }
}
