<?php
/**
 * File containing the LegacyMapper class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\SiteAccess;

use eZ\Publish\MVC\MVCEvents,
    eZ\Publish\MVC\Event\PostSiteAccessMatchEvent,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    \eZSiteAccess;

/**
 * Maps the siteAccess object to the legacy access array.
 */
class LegacyMapper implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 128 )
        );
    }

    /**
     * Maps matched siteaccess to the legacy access array
     *
     * @param \eZ\Publish\MVC\Event\PostSiteAccessMatchEvent $event
     */
    public function onSiteAccessMatch( PostSiteAccessMatchEvent $event )
    {
        $siteAccess = $event->getSiteAccess();
        $request = $event->getRequest();
        $uriPart = array();

        // Convert matching type
        switch ( $siteAccess->matchingType )
        {
            case 'default':
                $legacyAccessType = eZSiteAccess::TYPE_DEFAULT;
                break;

            case 'env':
                $legacyAccessType = eZSiteAccess::TYPE_SERVER_VAR;
                break;

            case 'uri:map':
            case 'uri:element':
            case 'uri:text':
            case 'uri:regexp':
                $legacyAccessType = eZSiteAccess::TYPE_URI;
                break;

            case 'host:map':
            case 'host:element':
            case 'host:text':
            case 'host:regexp':
                $legacyAccessType = eZSiteAccess::TYPE_HTTP_HOST;
                break;

            case 'port':
                $legacyAccessType = eZSiteAccess::TYPE_PORT;
                break;

            default:
                $legacyAccessType = eZSiteAccess::TYPE_CUSTOM;
        }

        // uri_part
        $pathinfo = $request->getPathInfo();
        $semanticPathinfo = $request->attributes->get( 'semanticPathinfo', $pathinfo );
        if ( $pathinfo != $semanticPathinfo )
        {
            $aPathinfo = explode( '/', substr( $pathinfo, 1 ) );
            $aSemanticPathinfo = explode( '/', substr( $semanticPathinfo, 1 ) );
            $uriPart = array_diff( $aPathinfo, $aSemanticPathinfo );
        }

        $request->attributes->set(
            'legacySiteaccess',
            array(
                'name'      => $siteAccess->name,
                'type'      => $legacyAccessType,
                'uri_part'  => $uriPart
            )
        );
    }
}
