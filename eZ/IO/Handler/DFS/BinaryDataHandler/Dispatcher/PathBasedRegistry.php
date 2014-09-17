<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\BinaryDataHandler\Dispatcher;

use EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\BinaryDataHandler;
use OutOfRangeException;

class PathBasedRegistry implements RegistryInterface
{
    /**
     * Handlers based on path (as key)
     * @var BinaryDataHandler[string] */
    private $pathHandlers = array();

    /**
     * The default handler, used when no {@see $pathHandlers} matches
     * @var BinaryDataHandler
     */
    private $defaultHandler;

    /**
     * @param BinaryDataHandler $defaultHandler
     * @param BinaryDataHandler[] $pathHandlers
     */
    public function __construct( BinaryDataHandler $defaultHandler, array $pathHandlers = array() )
    {
        foreach ( $pathHandlers as $supportedPath => $handler )
        {
            if ( !$handler instanceof BinaryDataHandler )
            {
                throw new \InvalidArgumentException( get_class( $handler ) . " does not implement BinaryDataHandler interface" );
            }
        }

        $this->defaultHandler = $defaultHandler;
        $this->pathHandlers = $pathHandlers;
    }

    /**
     * Returns the FSHandler for $path
     * @param $path
     * @return BinaryDataHandler
     * @throws OutOfRangeException If no handler supports $path
     */
    public function getHandler( $path )
    {
        foreach ( $this->pathHandlers as $supportedPath => $handler )
        {
            if ( strstr( $path, $supportedPath ) !== false )
            {
                return $handler;
            }
        }

        return $this->defaultHandler;
    }
}
