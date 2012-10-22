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
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Maps configuration parameters to the legacy parameters
 */
class Configuration implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct( ConfigResolverInterface $configResolver, ContainerInterface $container )
    {
        $this->configResolver = $configResolver;
        $this->container = $container;
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
     *
     * @todo Cache computed settings somehow
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

        $settings += $this->getImageSettings();

        $event->getParameters()->set(
            "injected-settings",
            $settings + (array)$event->getParameters()->get( "injected-settings" )
        );
    }

    private function getImageSettings()
    {
        // Basic settings
        $imageSettings = array(
            'image.ini/FileSettings/TemporaryDir'       => $this->configResolver->getParameter( 'image.temporary_dir' ),
            'image.ini/FileSettings/PublishedImages'    => $this->configResolver->getParameter( 'image.published_images_dir' ),
            'image.ini/FileSettings/VersionedImages'    => $this->configResolver->getParameter( 'image.versioned_images_dir' ),
            'image.ini/AliasSettings/AliasList'         => array(),
        );

        // Aliases configuration
        foreach ( $this->configResolver->getParameter( 'image_variations' ) as $aliasName => $aliasSettings )
        {
            $imageSettings['image.ini/AliasSettings/AliasList'][] = $aliasName;
            if ( isset( $aliasSettings['reference'] ) )
                $imageSettings["image.ini/$aliasName/Reference"] = $aliasSettings['reference'];

            foreach ( $aliasSettings['filters'] as $filter )
            {
                $imageSettings["image.ini/$aliasName/Filters"][] = $filter['name'] . '=' . implode( ';', $filter['params'] );
            }
        }

        // ImageMagick configuration
        $imageMagickEnabled = $this->container->getParameter( 'ezpublish.image.imagemagick.enabled' );
        $imageSettings['image.ini/ImageMagick/IsEnabled'] = $imageMagickEnabled ? 'true' : 'false';
        $imageSettings['image.ini/ImageMagick/ExecutablePath'] = $this->container->getParameter( 'ezpublish.image.imagemagick.executable_path' );
        $imageSettings['image.ini/ImageMagick/Executable'] = $this->container->getParameter( 'ezpublish.image.imagemagick.executable' );
        $imageSettings['image.ini/ImageMagick/PreParameters'] = $this->configResolver->getParameter( 'imagemagick.pre_parameters' );
        $imageSettings['image.ini/ImageMagick/PostParameters'] = $this->configResolver->getParameter( 'imagemagick.post_parameters' );
        $imageSettings['image.ini/ImageMagick/Filters'] = array();
        foreach ( $this->container->getParameter( 'ezpublish.image.imagemagick.filters' ) as $filterName => $filter )
        {
            $imageSettings['image.ini/ImageMagick/Filters'][] = "$filterName=" . strtr( $filter, array( '{' => '%', '}' => '' ) );
        }

        return $imageSettings;
    }
}
