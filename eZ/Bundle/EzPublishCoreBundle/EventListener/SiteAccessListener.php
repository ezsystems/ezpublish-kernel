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
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * SiteAccess match listener.
 */
class SiteAccessListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;
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

        if ( $this->container->hasParameter( "ezpublish.siteaccess.config.$siteAccess->name" ) )
        {
            $siteAccess->attributes->add(
                $this->container->getParameter( "ezpublish.siteaccess.config.$siteAccess->name" )
            );
        }
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

        $viewParameters = array();
        $vpString = substr( $pathinfo, $vpStart + 1 );
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

        // Now remove the view parameters string from $semanticPathinfo
        $pathinfo = substr( $pathinfo, 0, $vpStart );
        return array( $pathinfo, $viewParameters, "/$vpString" );
    }
}
