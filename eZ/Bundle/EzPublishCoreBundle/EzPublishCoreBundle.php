<?php
/**
 * File containing the EzPublishCoreBundle class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AsseticPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ComplexSettingsPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ConfigResolverParameterPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\FieldTypeParameterProviderRegistryPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\FragmentPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\IdentityDefinerPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ImaginePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RegisterStorageEnginePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LegacyStorageEnginePass;
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
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParser;
use eZ\Publish\Core\Base\Container\Compiler\FieldTypeCollectionPass;
use eZ\Publish\Core\Base\Container\Compiler\RegisterLimitationTypePass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\ExternalStorageRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy\CriteriaConverterPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy\CriterionFieldValueHandlerRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy\FieldValueConverterRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy\RoleLimitationConverterPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy\SortClauseConverterPass;
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
        $container->addCompilerPass( new FieldTypeCollectionPass );
        $container->addCompilerPass( new FieldTypeParameterProviderRegistryPass );
        $container->addCompilerPass( new ChainRoutingPass );
        $container->addCompilerPass( new ChainConfigResolverPass );
        $container->addCompilerPass( new RegisterLimitationTypePass );
        $container->addCompilerPass( new RegisterStorageEnginePass );
        $container->addCompilerPass( new LegacyStorageEnginePass );
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
        $container->addCompilerPass( new ImaginePass );
        $container->addCompilerPass( new ComplexSettingsPass( new ComplexSettingParser() ) );
        $container->addCompilerPass( new ConfigResolverParameterPass( new DynamicSettingParser() ) );
        $container->addCompilerPass( new AsseticPass() );

        // Storage passes
        $container->addCompilerPass( new ExternalStorageRegistryPass );
        // Legacy Storage passes
        $container->addCompilerPass( new CriteriaConverterPass );
        $container->addCompilerPass( new CriterionFieldValueHandlerRegistryPass );
        $container->addCompilerPass( new FieldValueConverterRegistryPass );
        $container->addCompilerPass( new RoleLimitationConverterPass );
        $container->addCompilerPass( new SortClauseConverterPass );

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
                    new ConfigParser\FieldType\RichText,
                    new ConfigParser\FieldType\XmlText,
                    new ConfigParser\FieldTemplates,
                    new ConfigParser\FieldDefinitionSettingsTemplates,
                    new ConfigParser\Image,
                    new ConfigParser\Page,
                    new ConfigParser\Languages,
                    new ConfigParser\IO
                )
            );
        }

        return $this->extension;
    }
}
