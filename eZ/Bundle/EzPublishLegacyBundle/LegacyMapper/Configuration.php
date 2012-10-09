<?php
/**
 * File containing the Session class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\Core\MVC\Legacy\LegacyEvents,
    eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelWebHandlerEvent,
    eZ\Publish\Core\MVC\ConfigResolverInterface,
    Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Maps configuration parameters to the legacy parameters
 */
class Configuration implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    public function __construct( ConfigResolverInterface $configResolver )
    {
        $this->configResolver = $configResolver;
    }


    public static function getSubscribedEvents()
    {
        return array(
            LegacyEvents::PRE_BUILD_LEGACY_KERNEL_WEB => array( "onBuildKernelWebHandler", 128 )
        );
    }

    /**
     * Adds settings to the parameters that will be injected into the legacy kernel
     *
     * @param \eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelWebHandlerEvent $event
     */
    public function onBuildKernelWebHandler( PreBuildKernelWebHandlerEvent $event )
    {
        $databaseSettings = $this->configResolver->getParameter( "database" );
        $settings = array();
        foreach (
            array(
                "server" => "Server",
                "port" => "Port",
                "user" => "User",
                "password" => "Password",
                "database_name" => "Database",
                "type" => "DatabaseImplementation",
            ) as $key => $iniKey
        )
        {
            if ( isset( $databaseSettings[$key] ) )
                $settings["site.ini/DatabaseSettings/$iniKey"] = $databaseSettings[$key];
        }

        $event->getParameters()->set(
            "injected-settings",
            $settings + (array)$event->getParameters()->get( "injected-settings" )
        );
    }
}
