<?php

/**
 * File containing the EzPublishCoreBundle class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\BinaryContentDownloadPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ComplexSettingsPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ConfigResolverParameterPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ConsoleCacheWarmupPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\FieldTypeParameterProviderRegistryPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\FragmentPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\NotificationRendererPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\PlaceholderProviderPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ImaginePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\QueryTypePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RegisterSearchEngineIndexerPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RegisterSearchEnginePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RegisterStorageEnginePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainRoutingPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainConfigResolverPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LocalePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ContentViewPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LocationViewPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RouterPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\SecurityPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\SignalSlotPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\SlugConverterConfigurationPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\TranslationCollectorPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ViewProvidersPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RichTextHtml5ConverterPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\StorageConnectionPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParser;
use eZ\Publish\Core\Base\Container\Compiler\FieldTypeCollectionPass;
use eZ\Publish\Core\Base\Container\Compiler\GenericFieldTypeConverterPass;
use eZ\Publish\Core\Base\Container\Compiler\RegisterLimitationTypePass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\ExternalStorageRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy\FieldValueConverterRegistryPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy\RoleLimitationConverterPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser as ConfigParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\HttpBasicFactory;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\URLHandlerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EzPublishCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new FieldTypeCollectionPass(), PassConfig::TYPE_OPTIMIZE);
        $container->addCompilerPass(new GenericFieldTypeConverterPass(), PassConfig::TYPE_OPTIMIZE);
        $container->addCompilerPass(new FieldTypeParameterProviderRegistryPass());
        $container->addCompilerPass(new ChainRoutingPass());
        $container->addCompilerPass(new ChainConfigResolverPass());
        $container->addCompilerPass(new RegisterLimitationTypePass());
        $container->addCompilerPass(new RegisterStorageEnginePass());
        $container->addCompilerPass(new RegisterSearchEnginePass());
        $container->addCompilerPass(new RegisterSearchEngineIndexerPass());
        $container->addCompilerPass(new LocalePass());
        $container->addCompilerPass(new ContentViewPass());
        $container->addCompilerPass(new LocationViewPass());
        $container->addCompilerPass(new SignalSlotPass());
        $container->addCompilerPass(new RouterPass());
        $container->addCompilerPass(new SecurityPass());
        $container->addCompilerPass(new RichTextHtml5ConverterPass());
        $container->addCompilerPass(new FragmentPass());
        $container->addCompilerPass(new StorageConnectionPass());
        $container->addCompilerPass(new ImaginePass());
        $container->addCompilerPass(new ComplexSettingsPass(new ComplexSettingParser()));
        $container->addCompilerPass(new ConfigResolverParameterPass(new DynamicSettingParser()));
        $container->addCompilerPass(new URLHandlerPass());
        $container->addCompilerPass(new BinaryContentDownloadPass());
        $container->addCompilerPass(new ViewProvidersPass());
        $container->addCompilerPass(new PlaceholderProviderPass());
        $container->addCompilerPass(new NotificationRendererPass());
        $container->addCompilerPass(new ConsoleCacheWarmupPass());

        // Storage passes
        $container->addCompilerPass(new ExternalStorageRegistryPass());
        // Legacy Storage passes
        $container->addCompilerPass(new FieldValueConverterRegistryPass());
        $container->addCompilerPass(new RoleLimitationConverterPass());
        $container->addCompilerPass(new QueryTypePass());

        $securityExtension = $container->getExtension('security');
        $securityExtension->addSecurityListenerFactory(new HttpBasicFactory());
        $container->addCompilerPass(new TranslationCollectorPass());
        $container->addCompilerPass(new SlugConverterConfigurationPass());

        if (!$container->hasExtension('ezrichtext')) {
            $this->getContainerExtension()->addConfigParser(new ConfigParser\FieldType\RichText());
        }
    }

    public function getContainerExtension()
    {
        if (!isset($this->extension)) {
            $this->extension = new EzPublishCoreExtension([
                // LocationView config parser needs to be specified AFTER ContentView config
                // parser since it is used to convert location view override rules to content
                // view override rules. If it were specified before, ContentView provider would
                // just undo the conversion LocationView did.
                new ConfigParser\ContentView(),
                new ConfigParser\LocationView(),
                new ConfigParser\Common(),
                new ConfigParser\Content(),
                new ConfigParser\FieldType\ImageAsset(),
                new ConfigParser\FieldTemplates(),
                new ConfigParser\FieldEditTemplates(),
                new ConfigParser\FieldDefinitionSettingsTemplates(),
                new ConfigParser\FieldDefinitionEditTemplates(),
                new ConfigParser\Image(),
                new ConfigParser\Languages(),
                new ConfigParser\IO(new ComplexSettingParser()),
                new ConfigParser\UrlChecker(),
            ]);
        }

        return $this->extension;
    }
}
