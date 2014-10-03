<?php
/**
 * File containing the IOServiceFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishIOBundle\ApiLoader;

use eZ\Publish\Core\IO\Handler as IoHandlerInterface;
use eZ\Publish\Core\IO\Handler\Filesystem;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\SPI\IO\MimeTypeDetector;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IOFactory extends ContainerAware
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    /** @var string */
    protected $IOServiceClass = 'eZ\\Publish\\Core\\IO\\IOService';

    /** @var MimeTypeDetector */
    protected $mimeTypeDetector;

    /** @var ContainerInterface */
    protected $container;

    /**
     * Map of io handler alias => io handler service id
     * @var array
     */
    private $ioHandlersMap;

    /**
     * Constructs a new IOServiceFactory
     *
     * @param ConfigResolverInterface $configResolver
     * @param \eZ\Publish\SPI\IO\MimeTypeDetector $mimeTypeDetector
     */
    public function __construct( ConfigResolverInterface $configResolver, MimeTypeDetector $mimeTypeDetector )
    {
        $this->configResolver = $configResolver;
        $this->mimeTypeDetector = $mimeTypeDetector;
    }

    /**
     * Returns a new IOService instance with the config string in $prefixSetting as a prefix
     *
     * @param IoHandlerInterface $IOHandler
     * @param bool|string $prefixSetting
     *
     * @return \eZ\Publish\Core\IO\IOService
     */
    public function getService( IoHandlerInterface $IOHandler, $prefixSetting = false )
    {
        $settings = array();

        if ( $prefixSetting )
        {
            $settings['prefix'] = $this->configResolver->getParameter( $prefixSetting );
        }

        return new $this->IOServiceClass( $IOHandler, $this->mimeTypeDetector, $settings );
    }

    /**
     * Returns an IOHandler instance
     *
     * @param string $handlerClass The IOHandler class to instantiate
     * @param string|array $storageDirectorySetting Setting(s) that build-up the storage directory
     *
     * @return mixed
     */
    public function getHandler( $handlerClass, $storageDirectorySetting )
    {
        if ( is_string( $storageDirectorySetting ) )
        {
            $storageDirectory = $this->configResolver->getParameter( $storageDirectorySetting );
        }
        else if ( is_array( $storageDirectorySetting ) )
        {
            $storageDirectoryParts = array();
            foreach ( $storageDirectorySetting as $setting )
            {
                $storageDirectoryParts[] = $this->configResolver->getParameter( $setting );
            }
            $storageDirectory = implode( '/', $storageDirectoryParts );
        }
        return new $handlerClass( $storageDirectory );
    }

    /**
     * Builds the filesystem IO handler based on config resolver settings
     * @return Filesystem
     */
    public function buildFilesystemHandler()
    {
        $storageDir = sprintf(
            '%s/%s/',
            trim( $this->configResolver->getParameter( 'var_dir' ), '/' ),
            trim( $this->configResolver->getParameter( 'storage_dir' ), '/' )
        );

        return new Filesystem(
            $this->mimeTypeDetector,
            array(
                'storage_dir' => $storageDir,
                'root_dir' => $this->container->getParameter( 'ezpublish_legacy.root_dir' )
            )
        );
    }

    /**
     * Returns the IO handler configured for the scope
     * @return IOHandlerInterface
     */
    public function buildConfiguredHandler()
    {
        $handlerAlias = $this->configResolver->getParameter( 'handler', 'ez_io' );
        if ( !isset( $this->ioHandlersMap[$handlerAlias] ) )
        {
            throw new InvalidConfigurationException( "No IO handler found for alias $handlerAlias" );
        }

        return $this->container->get( $this->ioHandlersMap[$handlerAlias] );
    }

    /**
     * @param array $map Associative array of handler alias => handler service id
     */
    public function setHandlersMap( array $map )
    {
        $this->ioHandlersMap = $map;
    }
}
