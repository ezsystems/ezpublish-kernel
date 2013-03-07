<?php
/**
 * File containing the Configuration class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger;
use eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger;
use ezpEvent;
use ezxFormToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger
     */
    private $gatewayCachePurger;

    /**
     * @var \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger
     */
    private $persistenceCachePurger;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $options;

    public function __construct( ConfigResolverInterface $configResolver, GatewayCachePurger $gatewayCachePurger, PersistenceCachePurger $persistenceCachePurger, ContainerInterface $container, array $options = array() )
    {
        $this->configResolver = $configResolver;
        $this->gatewayCachePurger = $gatewayCachePurger;
        $this->persistenceCachePurger = $persistenceCachePurger;
        $this->container = $container;
        $this->options = $options;
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
                "socket" => "Socket"
            ) as $key => $iniKey
        )
        {
            if ( isset( $databaseSettings[$key] ) )
            {
                $settings["site.ini/DatabaseSettings/$iniKey"] = $databaseSettings[$key];
            }
            // Some settings need specific values when not present.
            else
            {
                switch ( $key )
                {
                    case "socket":
                        $settings["site.ini/DatabaseSettings/$iniKey"] = "disabled";
                        break;
                }
            }
        }

        // Image settings
        $settings += $this->getImageSettings();
        // File settings
        $settings += array(
            'site.ini/FileSettings/VarDir'      => $this->configResolver->getParameter( 'var_dir' ),
            'site.ini/FileSettings/StorageDir'  => $this->configResolver->getParameter( 'storage_dir' )
        );

        $event->getParameters()->set(
            "injected-settings",
            $settings + (array)$event->getParameters()->get( "injected-settings" )
        );

        // Inject csrf protection settings to make sure legacy & symfony stack work together
        if (
            $this->container->hasParameter( 'form.type_extension.csrf.enabled' ) &&
            $this->container->getParameter( 'form.type_extension.csrf.enabled' )
        )
        {
            ezxFormToken::setSecret( $this->container->getParameter( 'kernel.secret' ) );
            ezxFormToken::setFormField( $this->container->getParameter( 'form.type_extension.csrf.field_name' ) );
        }

        // Register http cache content/cache event listener
        ezpEvent::getInstance()->attach( 'content/cache', array( $this->gatewayCachePurger, 'purge' ) );
        ezpEvent::getInstance()->attach( 'content/cache/all', array( $this->gatewayCachePurger, 'purgeAll' ) );

        // Register persistence cache event listeners
        ezpEvent::getInstance()->attach( 'content/cache', array( $this->persistenceCachePurger, 'content' ) );
        ezpEvent::getInstance()->attach( 'content/cache/all', array( $this->persistenceCachePurger, 'all' ) );
        ezpEvent::getInstance()->attach( 'content/class/cache/all', array( $this->persistenceCachePurger, 'contentType' ) );
        ezpEvent::getInstance()->attach( 'content/class/cache', array( $this->persistenceCachePurger, 'contentType' ) );
        ezpEvent::getInstance()->attach( 'content/class/group/cache', array( $this->persistenceCachePurger, 'contentTypeGroup' ) );
        ezpEvent::getInstance()->attach( 'content/section/cache', array( $this->persistenceCachePurger, 'section' ) );
        ezpEvent::getInstance()->attach( 'user/cache/all', array( $this->persistenceCachePurger, 'user' ) );
        ezpEvent::getInstance()->attach( 'content/translations/cache', array( $this->persistenceCachePurger, 'languages' ) );
    }

    private function getImageSettings()
    {
        $imageSettings = array(
            // Basic settings
            'image.ini/FileSettings/TemporaryDir'       => $this->configResolver->getParameter( 'image.temporary_dir' ),
            'image.ini/FileSettings/PublishedImages'    => $this->configResolver->getParameter( 'image.published_images_dir' ),
            'image.ini/FileSettings/VersionedImages'    => $this->configResolver->getParameter( 'image.versioned_images_dir' ),
            'image.ini/AliasSettings/AliasList'         => array(),
            // ImageMagick configuration
            'image.ini/ImageMagick/IsEnabled'           => $this->options['imagemagick_enabled'] ? 'true' : 'false',
            'image.ini/ImageMagick/ExecutablePath'      => $this->options['imagemagick_executable_path'],
            'image.ini/ImageMagick/Executable'          => $this->options['imagemagick_executable'],
            'image.ini/ImageMagick/PreParameters'       => $this->configResolver->getParameter( 'imagemagick.pre_parameters' ),
            'image.ini/ImageMagick/PostParameters'      => $this->configResolver->getParameter( 'imagemagick.post_parameters' ),
            'image.ini/ImageMagick/Filters'             => array()
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

        foreach ( $this->options['imagemagick_filters'] as $filterName => $filter )
        {
            $imageSettings['image.ini/ImageMagick/Filters'][] = "$filterName=" . strtr( $filter, array( '{' => '%', '}' => '' ) );
        }

        return $imageSettings;
    }
}
