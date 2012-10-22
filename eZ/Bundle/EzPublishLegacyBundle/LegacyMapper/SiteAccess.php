<?php
/**
 * File containing the SiteAccess class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\Core\MVC\Legacy\LegacyEvents,
    eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelWebHandlerEvent,
    \eZSiteAccess,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Maps the SiteAccess object to the legacy parameters
 */
class SiteAccess implements EventSubscriberInterface
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
            LegacyEvents::PRE_BUILD_LEGACY_KERNEL_WEB => array( 'onBuildKernelWebHandler', 128 )
        );
    }

    /**
     * Maps matched siteaccess to the legacy parameters
     *
     * @param \eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelWebHandlerEvent $event
     * @return void
     */
    public function onBuildKernelWebHandler( PreBuildKernelWebHandlerEvent $event )
    {
        $siteAccess = $this->container->get( 'ezpublish.siteaccess' );
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
        $pathinfo = str_replace( $request->attributes->get( 'viewParametersString' ), '', $request->getPathInfo() );
        $semanticPathinfo = $request->attributes->get( 'semanticPathinfo', $pathinfo );
        if ( $pathinfo != $semanticPathinfo )
        {
            $aPathinfo = explode( '/', substr( $pathinfo, 1 ) );
            $aSemanticPathinfo = explode( '/', substr( $semanticPathinfo, 1 ) );
            $uriPart = array_diff( $aPathinfo, $aSemanticPathinfo );
        }

        $event->getParameters()->set(
            'siteaccess',
            array(
                'name' => $siteAccess->name,
                'type' => $legacyAccessType,
                'uri_part' => $uriPart
            )
        );
    }
}
