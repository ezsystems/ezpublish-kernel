<?php
/**
 * File containing the EzPublishCoreBundle class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AddFieldTypePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RegisterStorageEnginePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RegisterLimitationTypePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LegacyStorageEnginePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainRoutingPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainConfigResolverPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LocalePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ContentViewPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LocationViewPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\BlockViewPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser as ConfigParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\Factory as EzPublishSecurityFactory;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\HttpBasicFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
        $container->addCompilerPass( new LocalePass );
        $container->addCompilerPass( new ContentViewPass );
        $container->addCompilerPass( new LocationViewPass );
        $container->addCompilerPass( new BlockViewPass );

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
                    new ConfigParser\ContentView,
                    new ConfigParser\BlockView,
                    new ConfigParser\Common,
                    new ConfigParser\Content,
                    new ConfigParser\FieldTemplates,
                    new ConfigParser\FieldDefinitionSettingsTemplates,
                    new ConfigParser\Image,
                    new ConfigParser\Page
                )
            );
        }

        return $this->extension;
    }
}
