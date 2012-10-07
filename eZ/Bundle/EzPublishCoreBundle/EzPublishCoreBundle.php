<?php
/**
 * File containing the EzPublishCoreBundle class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AddFieldTypePass,
    eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RegisterStorageEnginePass,
    eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RegisterLimitationTypePass,
    eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LegacyStorageEnginePass,
    eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainRoutingPass,
    eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainConfigResolverPass,
    eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\TwigTweaksPass,
    eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ContentViewPass,
    eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension,
    eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser as ConfigParser,
    eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\Factory as EzPublishSecurityFactory,
    eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\HttpBasicFactory,
    Symfony\Component\HttpKernel\Bundle\Bundle,
    Symfony\Component\DependencyInjection\ContainerBuilder;

class EzPublishCoreBundle extends Bundle
{
    public function build( ContainerBuilder $container )
    {
        parent::build( $container );
        $container->addCompilerPass( new ChainRoutingPass );
        $container->addCompilerPass( new ChainConfigResolverPass );
        $container->addCompilerPass( new AddFieldTypePass );
        $container->addCompilerPass( new RegisterLimitationTypePass );
        $container->addCompilerPass( new RegisterStorageEnginePass );
        $container->addCompilerPass( new LegacyStorageEnginePass );
        $container->addCompilerPass( new TwigTweaksPass );
        $container->addCompilerPass( new ContentViewPass );

        $securityExtension = $container->getExtension( 'security' );
        $securityExtension->addSecurityListenerFactory( new EzPublishSecurityFactory );
        $securityExtension->addSecurityListenerFactory( new HttpBasicFactory );
    }

    public function getContainerExtension()
    {
        if ( !isset( $this->extension ) )
        {
            $this->extension = new EzPublishCoreExtension(
                array(
                     new ConfigParser\LocationView,
                     new ConfigParser\Common,
                     new ConfigParser\Content,
                     new ConfigParser\FieldTemplates,
                )
            );
        }

        return $this->extension;
    }
}
