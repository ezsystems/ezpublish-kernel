<?php
/**
 * File containing the SiteAccess class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelWebHandlerEvent;
use eZSiteAccess;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Maps the SiteAccess object to the legacy parameters
 */
class SiteAccess extends ContainerAware implements EventSubscriberInterface
{
    protected $options = array();

    public function __construct( array $options = array() )
    {
        $this->options = $options;
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
     *
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
            $uriPart = array_diff( $aPathinfo, $aSemanticPathinfo, $this->getFragmentPathItems() );
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

    /**
     * Returns an array with all the components of the fragment_path option
     * @return array
     */
    protected function getFragmentPathItems()
    {
        if ( isset( $this->options['fragment_path'] ) )
        {
            return explode( '/', trim( $this->options['fragment_path'], '/' ) );
        }
        return array( '_fragment' );
    }
}
