<?php
/**
 * File containing the SiteAccessListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * SiteAccess match listener.
 */
class SiteAccessListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $defaultRouter;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator
     */
    private $urlAliasGenerator;

    public function __construct( ContainerInterface $container, RouterInterface $defaultRouter, UrlAliasGenerator $urlAliasGenerator )
    {
        $this->container = $container;
        $this->defaultRouter = $defaultRouter;
        $this->urlAliasGenerator = $urlAliasGenerator;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 255 )
        );
    }

    public function onSiteAccessMatch( PostSiteAccessMatchEvent $event )
    {
        $request = $event->getRequest();
        $siteAccess = $event->getSiteAccess();
        $this->container->set( 'ezpublish.siteaccess', $siteAccess );
        if ( $this->urlAliasGenerator instanceof SiteAccessAware )
            $this->urlAliasGenerator->setSiteAccess( $siteAccess );
        if ( $this->defaultRouter instanceof SiteAccessAware )
            $this->defaultRouter->setSiteAccess( $siteAccess );

        // We already have semanticPathinfo (sub-request)
        if ( $request->attributes->has( 'semanticPathinfo' ) )
        {
            $vpString = $request->attributes->get( 'viewParametersString' );
            if ( !empty( $vpString ) )
            {
                $request->attributes->set(
                    'viewParameters',
                    $this->generateViewParametersArray( $vpString )
                );
            }
            else
            {
                $request->attributes->set( 'viewParametersString', '' );
                $request->attributes->set( 'viewParameters', array() );
            }

            return;
        }

        // Analyse the pathinfo if needed since it might contain the siteaccess (i.e. like in URI mode)
        $pathinfo = $request->getPathInfo();
        if ( $siteAccess->matcher instanceof URILexer )
        {
            $semanticPathinfo = $siteAccess->matcher->analyseURI( $pathinfo );
        }
        else
        {
            $semanticPathinfo = $pathinfo;
        }

        // Get view parameters and cleaned up pathinfo (without view parameters string)
        list( $semanticPathinfo, $viewParameters, $viewParametersString ) = $this->getViewParameters( $semanticPathinfo );

        // Storing the modified pathinfo in 'semanticPathinfo' request attribute, to keep a trace of it.
        // Routers implementing RequestMatcherInterface should thus use this attribute instead of the original pathinfo
        $request->attributes->set( 'semanticPathinfo', $semanticPathinfo );
        $request->attributes->set( 'viewParameters', $viewParameters );
        $request->attributes->set( 'viewParametersString', $viewParametersString );
    }

    /**
     * Extracts view parameters from $pathinfo.
     * In the pathinfo, view parameters are in the form /(param_name)/param_value.
     *
     * @param string $pathinfo
     *
     * @return array First element is the cleaned up pathinfo (without the view parameters string).
     *               Second element is the view parameters hash.
     *               Third element is the view parameters string (e.g. /(foo)/bar)
     */
    private function getViewParameters( $pathinfo )
    {
        // No view parameters, get out of here.
        if ( ( $vpStart = strpos( $pathinfo, '/(' ) ) === false )
        {
            return array( $pathinfo, array(), '' );
        }

        $vpString = substr( $pathinfo, $vpStart + 1 );
        $viewParameters = $this->generateViewParametersArray( $vpString );

        // Now remove the view parameters string from $semanticPathinfo
        $pathinfo = substr( $pathinfo, 0, $vpStart );
        return array( $pathinfo, $viewParameters, "/$vpString" );
    }

    /**
     * Generates the view parameters array from the view parameters string.
     *
     * @param $vpString
     *
     * @return array
     */
    private function generateViewParametersArray( $vpString )
    {
        $vpString = trim( $vpString, '/' );
        $viewParameters = array();

        $vpSegments = explode( '/', $vpString );
        for ( $i = 0, $iMax = count( $vpSegments ); $i < $iMax; ++$i )
        {
            if ( !isset( $vpSegments[$i] ) )
            {
                continue;
            }

            // View parameter name.
            // We extract it + the value from the following segment (next element in $vpSegments array)
            if ( $vpSegments[$i]{0} === '(' )
            {
                $paramName = str_replace( array( '(', ')' ), '', $vpSegments[$i] );
                // A value is present (e.g. /(foo)/bar)
                if ( isset( $vpSegments[$i + 1] ) )
                {
                    $viewParameters[$paramName] = $vpSegments[$i + 1];
                    unset( $vpSegments[$i + 1] );
                }
                // No value (e.g. /(foo)) => set it to empty string
                else
                {
                    $viewParameters[$paramName] = '';
                }
            }
            // Orphan segment (no previous parameter name), e.g. /(foo)/bar/baz
            // Add it to the previous parameter.
            else if ( isset( $paramName ) )
            {
                $viewParameters[$paramName] .= '/' . $vpSegments[$i];
            }
        }

        return $viewParameters;
    }
}
