<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers IO handlers
 */
class IOHandlerPass implements CompilerPassInterface
{
    /**
     * @throws \LogicException
     */
    public function process( ContainerBuilder $container )
    {
        $ioHandlersMap = array();

        foreach ( $container->findTaggedServiceIds( 'ezpublish.io_handler' ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['alias'] ) )
                    throw new LogicException( 'ezpublish.io_handler service tag needs an "alias" attribute to identify the handler. None given.' );

                $ioHandlersMap[$attribute['alias']] = $id;
            }
        }

        $container->setParameter( 'ezpublish.io.handlers_map', $ioHandlersMap );
    }
}
