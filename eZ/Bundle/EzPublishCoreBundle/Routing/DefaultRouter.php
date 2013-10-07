<?php
/**
 * File containing the DefaultRouter class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Routing;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

/**
 * Extension of Symfony default router implementing RequestMatcherInterface
 */
class DefaultRouter extends Router implements RequestMatcherInterface, SiteAccessAware
{
    /**
     * @var SiteAccess
     */
    protected $siteAccess;

    protected $nonSiteAccessAwareRoutes = array();

    protected $legacyAwareRoutes = array();

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $serviceContainer;

    public function __construct( ContainerInterface $container, $resource, array $options = array(), RequestContext $context = null )
    {
        $this->serviceContainer = $container;
        parent::__construct( $container, $resource, $options, $context );
    }

    public function setSiteAccess( SiteAccess $siteAccess = null )
    {
        $this->siteAccess = $siteAccess;
    }

    /**
     * Injects route names that are not supposed to be SiteAccess aware.
     * i.e. Routes pointing to asset generation (like assetic).
     *
     * @param array $routes
     */
    public function setNonSiteAccessAwareRoutes( array $routes )
    {
        $this->nonSiteAccessAwareRoutes = $routes;
    }

    /**
     * Injectes route names that are allowed to run with legacy_mode: true.
     *
     * @param array $routes
     */
    public function setLegacyAwareRoutes( array $routes )
    {
        $this->legacyAwareRoutes = $routes;
    }

    /**
     * @return ConfigResolverInterface
     */
    protected function getConfigResolver()
    {
        return $this->serviceContainer->get( 'ezpublish.config.resolver' );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request The request to match
     *
     * @return array An array of parameters
     *
     * @throws ResourceNotFoundException If no matching resource could be found
     * @throws MethodNotAllowedException If a matching resource was found but the request method is not allowed
     */
    public function matchRequest( Request $request )
    {
        if ( $request->attributes->has( 'semanticPathinfo' ) )
        {
            $attributes = $this->match( $request->attributes->get( 'semanticPathinfo' ) );
        }
        else
        {
            $attributes = $this->match( $request->getPathInfo() );
        }

        if (
            $this->getConfigResolver()->getParameter( 'legacy_mode' ) === true
            && isset( $attributes['_route'] )
            && !$this->isLegacyAwareRoute( $attributes['_route'] )
        )
        {
            throw new ResourceNotFoundException( "Legacy mode activated, default router is bypassed" );
        }

        return $attributes;
    }

    public function generate( $name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH )
    {
        $url = parent::generate( $name, $parameters, $referenceType );
        if ( $this->isSiteAccessAwareRoute( $name ) && isset( $this->siteAccess ) && $this->siteAccess->matcher instanceof URILexer )
        {
            $context = $this->getContext();
            if ( $referenceType == self::ABSOLUTE_URL || $referenceType == self::NETWORK_PATH )
            {
                $scheme = $context->getScheme();
                $port = '';
                if ( $scheme === 'http' && $this->context->getHttpPort() != 80 )
                {
                    $port = ':' . $this->context->getHttpPort();
                }
                else if ( $scheme === 'https' && $this->context->getHttpsPort() != 443 )
                {
                    $port = ':' . $this->context->getHttpsPort();
                }

                $base = $context->getHost() . $port . $context->getBaseUrl();
            }
            else
            {
                $base = $context->getBaseUrl();
            }

            $linkUri = $base ? substr( $url, strpos( $url, $base ) + strlen( $base ) ) : $url;
            $url = str_replace( $linkUri, $this->siteAccess->matcher->analyseLink( $linkUri ), $url );
        }

        return $url;
    }

    /**
     * Checks if $routeName is a siteAccess aware route, and thus needs to have siteAccess URI prepended.
     * Will be used for link generation, only in the case of URI SiteAccess matching.
     *
     * @param $routeName
     *
     * @return bool
     */
    protected function isSiteAccessAwareRoute( $routeName )
    {
        foreach ( $this->nonSiteAccessAwareRoutes as $ignoredPrefix )
        {
            if ( strpos( $routeName, $ignoredPrefix ) === 0 )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if $routeName can be used in legacy mode.
     *
     * @param string $routeName
     *
     * @return bool
     */
    protected function isLegacyAwareRoute( $routeName )
    {
        foreach ( $this->legacyAwareRoutes as $legacyAwareRoute )
        {
            if ( strpos( $routeName, $legacyAwareRoute ) === 0 )
            {
                return true;
            }
        }

        return false;
    }
}
