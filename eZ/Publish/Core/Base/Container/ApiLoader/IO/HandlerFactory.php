<?php
/**
 * File containing the IOServiceFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishIOBundle\ApiLoader;

use eZ\Publish\Core\Base\Container\ApiLoader\IO\ParameterProvider;
use eZ\Publish\Core\IO\Handler as IoHandlerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class HandlerFactory
{
    /**
     * Map of io handler alias => io handler service id
     * @var array
     */
    private $ioHandlersMap;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * Constructs a new IOServiceFactory
     *
     * @param ConfigResolverInterface $configResolver
     * @param \eZ\Publish\SPI\IO\MimeTypeDetector $mimeTypeDetector
     */
    public function __construct( ParameterProvider $parameterProvider )
    {
        $this->parameterProvider = $parameterProvider;
    }

    /**
     * @param array $map Associative array of handler alias => handler service id
     */
    public function setMap( array $map )
    {
        $this->ioHandlersMap = $map;
    }

    /**
     * Returns an IOHandler instance
     *
     * @param string $handlerClass The IOHandler class to instanciate
     * @param string|array $storageDirectorySetting Setting(s) that build-up the storage directory
     *
     * @return mixed
     */
    public function buildFilesystemHandler( $handlerClass, $storageDirectorySetting )
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
     * Returns the IO handler configured for the scope
     * @return IOHandlerInterface
     */
    public function getConfiguredHandler()
    {
        $handlerAlias = $this->configResolver->getParameter( 'handler', 'ez_io' );
        if ( !isset( $this->ioHandlersMap[$handlerAlias] ) )
        {
            throw new InvalidConfigurationException( "No IO handler found for alias $handlerAlias" );
        }

        return $this->container->get( $this->ioHandlersMap[$handlerAlias] );
    }
}
