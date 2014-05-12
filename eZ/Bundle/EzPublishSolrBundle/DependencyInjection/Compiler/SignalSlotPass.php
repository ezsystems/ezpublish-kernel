<?php
/**
 * File containing the SignalSlotPass class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishSolrBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will attach Solr Storage slots to SignalDispatcher
 */
class SignalSlotPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.signalslot.signal_dispatcher' ) )
        {
            return;
        }

        $signalDispatcherDef = $container->getDefinition( 'ezpublish.signalslot.signal_dispatcher' );

        foreach ( $container->findTaggedServiceIds( 'ezpublish.persistence.solr.slot' ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['signal'] ) )
                {
                    throw new LogicException(
                        "Could not find 'signal' attribute on '$id' service, " .
                        "which is mandatory for services tagged as 'ezpublish.persistence.solr.slot'"
                    );
                }

                $signalDispatcherDef->addMethodCall(
                    'attach',
                    array(
                        $attribute['signal'],
                        new Reference( $id )
                    )
                );
            }
        }
    }
}
