<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\Base\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use LogicException;

/**
 * Registers IO handlers tagged as ezpublish.io_handler
 */
class IOHandlerTagPass implements CompilerPassInterface
{
    /**
     * @throws \LogicException If a service tag is incorrect
     */
    public function process( ContainerBuilder $container )
    {
        $container->setParameter(
            'ez_io.available_metadata_handler_types',
            $this->findHandlers( $container, 'ezpublish.io.metadata_handler' )
        );
        $container->setParameter(
            'ez_io.available_binarydata_handler_types',
            $this->findHandlers( $container, 'ezpublish.io.binarydata_handler' )
        );
    }

    /**
     * @param ContainerBuilder $container
     * @param string $tag
     * @return array Array of service alias => service id
     *
     * @throws \LogicException If a service tag is incorrect
     */
    protected function findHandlers( ContainerBuilder $container, $tag )
    {
        $map = array();
        foreach ( $container->findTaggedServiceIds( $tag ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['alias'] ) )
                {
                    throw new LogicException(
                        "$tag service tag needs an 'alias' attribute to identify the handler. None given for $id"
                    );
                }

                $map[$attribute['alias']] = $id;
            }
        }
        return $map;
    }
}
