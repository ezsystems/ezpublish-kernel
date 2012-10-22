<?php
/**
 * File containing the Configuration class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\Core\MVC\Legacy\LegacyEvents,
    eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent,
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
            LegacyEvents::PRE_BUILD_LEGACY_KERNEL => array( "onBuildKernel", 128 )
        );
    }

    /**
     * Adds settings to the parameters that will be injected into the legacy kernel
     *
     * @param \eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent $event
     * @return void
     */
    public function onBuildKernel( PreBuildKernelEvent $event )
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

        // File settings
        $settings['site.ini/FileSettings/VarDir'] = $this->configResolver->getParameter( 'var_dir' );
        $settings['site.ini/FileSettings/StorageDir'] = $this->configResolver->getParameter( 'storage_dir' );

        $event->getParameters()->set(
            "injected-settings",
            $settings + (array)$event->getParameters()->get( "injected-settings" )
        );
    }
}
