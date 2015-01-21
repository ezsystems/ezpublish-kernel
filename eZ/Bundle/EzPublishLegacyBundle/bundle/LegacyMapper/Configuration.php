<?php
/**
 * File containing the Configuration class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\Core\FieldType\Image\AliasCleanerInterface;
use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger;
use eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use ezpEvent;
use ezxFormToken;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use RuntimeException;

/**
 * Maps configuration parameters to the legacy parameters
 */
class Configuration extends ContainerAware implements EventSubscriberInterface
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
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator
     */
    private $urlAliasGenerator;

    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    private $legacyDbHandler;

    /**
     * @var array
     */
    private $options;

    /**
     * Disables the feature when set using setEnabled()
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * @var AliasCleanerInterface
     */
    private $aliasCleaner;

    public function __construct(
        ConfigResolverInterface $configResolver,
        GatewayCachePurger $gatewayCachePurger,
        PersistenceCachePurger $persistenceCachePurger,
        UrlAliasGenerator $urlAliasGenerator,
        DatabaseHandler $legacyDbHandler,
        AliasCleanerInterface $aliasCleaner,
        array $options = array()
    )
    {
        $this->configResolver = $configResolver;
        $this->gatewayCachePurger = $gatewayCachePurger;
        $this->persistenceCachePurger = $persistenceCachePurger;
        $this->urlAliasGenerator = $urlAliasGenerator;
        $this->legacyDbHandler = $legacyDbHandler;
        $this->aliasCleaner = $aliasCleaner;
        $this->options = $options;
    }

    /**
     * Toggles the feature
     *
     * @param bool $isEnabled
     */
    public function setEnabled( $isEnabled )
    {
        $this->enabled = (bool)$isEnabled;
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
     */
    public function onBuildKernel( PreBuildKernelEvent $event )
    {
        if ( !$this->enabled )
        {
            return;
        }

        $databaseSettings = $this->legacyDbHandler->getConnection()->getParams();
        $settings = array();
        foreach (
            array(
                "host" => "Server",
                "port" => "Port",
                "user" => "User",
                "password" => "Password",
                "dbname" => "Database",
                "unix_socket" => "Socket",
                "driver" => "DatabaseImplementation"
            ) as $key => $iniKey
        )
        {
            if ( isset( $databaseSettings[$key] ) )
            {
                $iniValue = $databaseSettings[$key];

                switch ( $key )
                {
                    case "driver":
                        $driverMap = array(
                            'pdo_mysql' => 'ezmysqli',
                            'pdo_pgsql' => 'ezpostgresql',
                            'oci8' => 'ezoracle'
                        );
                        if ( !isset( $driverMap[$iniValue] ) )
                        {
                            throw new RuntimeException(
                                "Could not map database driver to Legacy Stack database implementation.\n" .
                                "Expected one of '" . implode( "', '", array_keys( $driverMap ) ) . "', got '" .
                                $iniValue . "'."
                            );
                        }
                        $iniValue = $driverMap[$iniValue];
                        break;
                }

                $settings["site.ini/DatabaseSettings/$iniKey"] = $iniValue;
            }
            // Some settings need specific values when not present.
            else
            {
                switch ( $key )
                {
                    case "unix_socket":
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
        // Multisite settings (PathPrefix and co)
        $settings += $this->getMultiSiteSettings();

        // User settings
        $settings["site.ini/UserSettings/AnonymousUserID"] = $this->configResolver->getParameter( "anonymous_user_id" );

        // Cache settings
        // Enforce ViewCaching to be enabled in order to persistence/http cache to be purged correctly.
        $settings['site.ini/ContentSettings/ViewCaching'] = 'enabled';

        $event->getParameters()->set(
            "injected-settings",
            $settings + (array)$event->getParameters()->get( "injected-settings" )
        );

        if ( class_exists( 'ezxFormToken' ) )
        {
            // Inject csrf protection settings to make sure legacy & symfony stack work together
            if (
                $this->container->hasParameter( 'form.type_extension.csrf.enabled' ) &&
                $this->container->getParameter( 'form.type_extension.csrf.enabled' )
            )
            {
                ezxFormToken::setSecret( $this->container->getParameter( 'kernel.secret' ) );
                ezxFormToken::setFormField( $this->container->getParameter( 'form.type_extension.csrf.field_name' ) );
            }
            // csrf protection is disabled, disable it in legacy extension as well.
            else
            {
                ezxFormToken::setIsEnabled( false );
            }
        }

        // Register http cache content/cache event listener
        $ezpEvent = ezpEvent::getInstance();
        $ezpEvent->attach( 'content/cache', array( $this->gatewayCachePurger, 'purge' ) );
        $ezpEvent->attach( 'content/cache/all', array( $this->gatewayCachePurger, 'purgeAll' ) );

        // Register persistence cache event listeners
        $ezpEvent->attach( 'content/cache', array( $this->persistenceCachePurger, 'content' ) );
        $ezpEvent->attach( 'content/cache/all', array( $this->persistenceCachePurger, 'all' ) );
        $ezpEvent->attach( 'content/class/cache/all', array( $this->persistenceCachePurger, 'contentType' ) );
        $ezpEvent->attach( 'content/class/cache', array( $this->persistenceCachePurger, 'contentType' ) );
        $ezpEvent->attach( 'content/class/group/cache', array( $this->persistenceCachePurger, 'contentTypeGroup' ) );
        $ezpEvent->attach( 'content/section/cache', array( $this->persistenceCachePurger, 'section' ) );
        $ezpEvent->attach( 'user/cache/all', array( $this->persistenceCachePurger, 'user' ) );
        $ezpEvent->attach( 'content/translations/cache', array( $this->persistenceCachePurger, 'languages' ) );

        // Register image alias removal listeners
        $ezpEvent->attach( 'image/removeAliases', array( $this->aliasCleaner, 'removeAliases' ) );
        $ezpEvent->attach( 'image/trashAliases', array( $this->aliasCleaner, 'removeAliases' ) );
        $ezpEvent->attach( 'image/purgeAliases', array( $this->aliasCleaner, 'removeAliases' ) );
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
        $imageVariations = $this->configResolver->getParameter( 'image_variations' );
        foreach ( $imageVariations as $aliasName => $aliasSettings )
        {
            $imageSettings['image.ini/AliasSettings/AliasList'][] = $aliasName;
            if ( isset( $aliasSettings['reference'] ) )
                $imageSettings["image.ini/$aliasName/Reference"] = $aliasSettings['reference'];

            foreach ( $aliasSettings['filters'] as $filterName => $filter )
            {
                if ( !isset( $this->options['imagemagick_filters'][$filterName] ) )
                {
                    continue;
                }
                $imageSettings["image.ini/$aliasName/Filters"][] = $filterName . '=' . implode( ';', $filter );
            }
        }

        foreach ( $this->options['imagemagick_filters'] as $filterName => $filter )
        {
            $imageSettings['image.ini/ImageMagick/Filters'][] = "$filterName=" . strtr( $filter, array( '{' => '%', '}' => '' ) );
        }

        return $imageSettings;
    }

    private function getMultiSiteSettings()
    {
        $rootLocationId = $this->configResolver->getParameter( 'content.tree_root.location_id' );
        $defaultPage = $this->configResolver->getParameter( 'default_page' );
        if ( $rootLocationId === null )
        {
            return array();
        }

        $pathPrefix = trim( $this->urlAliasGenerator->getPathPrefixByRootLocationId( $rootLocationId ), '/' );
        $pathPrefixExcludeItems = array_map(
            function ( $value )
            {
                return trim( $value, '/' );
            },
            $this->configResolver->getParameter( 'content.tree_root.excluded_uri_prefixes' )
        );

        return array(
            'site.ini/SiteAccessSettings/PathPrefix'        => $pathPrefix,
            'site.ini/SiteAccessSettings/PathPrefixExclude' => $pathPrefixExcludeItems,
            'logfile.ini/AccessLogFileSettings/PathPrefix'  => $pathPrefix,
            'site.ini/SiteSettings/IndexPage'               => "/content/view/full/$rootLocationId/",
            'site.ini/SiteSettings/DefaultPage'             => $defaultPage !== null ? $defaultPage : "/content/view/full/$rootLocationId/",
            'content.ini/NodeSettings/RootNode'             => $rootLocationId,
        );
    }
}
