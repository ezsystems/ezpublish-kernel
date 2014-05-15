<?php
/**
 * File containing the GlobalHelper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Templating helper object globally accessible, through the "ezpublish" variable (in Twig).
 * Container is injected to be sure to lazy load underlying services and to avoid scope conflict.
 */
class GlobalHelper
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;
    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    public function __construct(
        ConfigResolverInterface $configResolver,
        LocationService $locationService,
        RouterInterface $router,
        TranslationHelper $translationHelper
    )
    {
        $this->configResolver = $configResolver;
        $this->locationService = $locationService;
        $this->router = $router;
        $this->translationHelper = $translationHelper;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setRequest( Request $request = null )
    {
        $this->request = $request;
    }

    /**
     * Returns the current siteaccess.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess|null
     */
    public function getSiteaccess()
    {
        if ( $this->request )
        {
            return $this->request->attributes->get( 'siteaccess' );
        }
    }

    /**
     * Returns the view parameters as a hash.
     *
     * @return array|null
     */
    public function getViewParameters()
    {
        if ( $this->request )
        {
            return $this->request->attributes->get( 'viewParameters' );
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
        if ( $this->request )
        {
            return $this->request->attributes->get( 'viewParametersString' );
        }
    }

    /**
     * Returns the requested URI string (aka semanticPathInfo).
     *
     * @return string
     */
    public function getRequestedUriString()
    {
        if ( $this->request )
        {
            return $this->request->attributes->get( 'semanticPathinfo' );
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
        if ( $this->request )
        {
            if ( $this->request->attributes->get( '_route' ) === UrlAliasRouter::URL_ALIAS_ROUTE_NAME )
            {
                return $this->router
                    ->generate(
                        '_ezpublishLocation',
                        array(
                            'locationId' => $this->request->attributes->get( 'locationId' ),
                            'viewType' => $this->request->attributes->get( 'viewType' )
                        )
                    );
            }

            return $this->getRequestedUriString();
        }
    }

    /**
     * Returns the root location.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getRootLocation()
    {
        return $this->locationService->loadLocation(
            $this->configResolver->getParameter( 'content.tree_root.location_id' )
        );
    }

    /**
     * Returns the translation SiteAccess for $language, or null if it cannot be found.
     *
     * @param string $language
     *
     * @return null|string
     */
    public function getTranslationSiteAccess( $language )
    {
        return $this->translationHelper->getTranslationSiteAccess( $language );
    }

    /**
     * Returns the list of available languages.
     *
     * @return array
     */
    public function getAvailableLanguages()
    {
        return $this->translationHelper->getAvailableLanguages();
    }

    /**
     * Returns the config resolver.
     *
     * @return \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    public function getConfigResolver()
    {
        return $this->configResolver;
    }
}
