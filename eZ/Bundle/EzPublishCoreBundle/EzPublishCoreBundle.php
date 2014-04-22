<?php
/**
 * File containing the EzPublishCoreBundle class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle;

use eZ\Publish\Core\Base\Container\Compiler\FieldTypeCollectionPass;
use eZ\Publish\Core\Base\Container\Compiler\RegisterLimitationTypePass;
use eZ\Publish\Core\Base\Container\Compiler\Storage;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\FieldTypeParameterProviderRegistryPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\FragmentPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\IdentityDefinerPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RegisterStorageEnginePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainRoutingPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainConfigResolverPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LocalePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ContentViewPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LocationViewPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\BlockViewPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\SecurityPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\SignalSlotPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\XmlTextConverterPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RichTextHtml5ConverterPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\StorageConnectionPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser as ConfigParser;
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
        $container->addCompilerPass( new FieldTypeParameterProviderRegistryPass );
        $container->addCompilerPass( new RegisterStorageEnginePass );
        $container->addCompilerPass( new LocalePass );
        $container->addCompilerPass( new ContentViewPass );
        $container->addCompilerPass( new LocationViewPass );
        $container->addCompilerPass( new BlockViewPass );
        $container->addCompilerPass( new SignalSlotPass );
        $container->addCompilerPass( new IdentityDefinerPass );
        $container->addCompilerPass( new XmlTextConverterPass );
        $container->addCompilerPass( new SecurityPass );
        $container->addCompilerPass( new RichTextHtml5ConverterPass );
        $container->addCompilerPass( new FragmentPass );
        $container->addCompilerPass( new StorageConnectionPass );

        $container->addCompilerPass( new FieldTypeCollectionPass );
        $container->addCompilerPass( new RegisterLimitationTypePass );
        $container->addCompilerPass( new Storage\ExternalStorageRegistryPass() );
        $container->addCompilerPass( new Storage\Legacy\FieldValueConverterRegistryPass );
        $container->addCompilerPass( new Storage\Legacy\CriterionFieldValueHandlerRegistryPass );
        $container->addCompilerPass( new Storage\Legacy\CriteriaConverterPass );
        $container->addCompilerPass( new Storage\Legacy\SortClauseConverterPass );
        $container->addCompilerPass( new Storage\Legacy\RoleLimitationConverterPass );

        $securityExtension = $container->getExtension( 'security' );
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
