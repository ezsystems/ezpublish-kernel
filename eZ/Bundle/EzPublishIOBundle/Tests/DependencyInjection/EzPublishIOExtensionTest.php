<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Tests\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainConfigResolverPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use eZ\Bundle\EzPublishIOBundle\DependencyInjection\EzPublishIOExtension;
use eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory;
use eZ\Publish\API\Repository\Tests\Container\Compiler\SetAllServicesPublicPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\Yaml\Yaml;

class EzPublishIOExtensionTest extends AbstractExtensionTestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../_fixtures';

    protected function getContainerExtensions(): array
    {
        $extension = new EzPublishIOExtension();
        $extension->addMetadataHandlerFactory('flysystem', new ConfigurationFactory\MetadataHandler\Flysystem());
        $extension->addBinarydataHandlerFactory('flysystem', new ConfigurationFactory\BinarydataHandler\Flysystem());

        return [$extension];
    }

    public function testParametersWithoutConfiguration()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('ez_io.metadata_handlers', []);
        $this->assertContainerBuilderHasParameter('ez_io.binarydata_handlers', []);
    }

    public function testParametersWithMetadataHandler()
    {
        $config = [
            'metadata_handlers' => [
                'my_metadata_handler' => ['flysystem' => ['adapter' => 'my_adapter']],
            ],
        ];
        $this->load($config);

        $this->assertContainerBuilderHasParameter('ez_io.binarydata_handlers', []);
        $this->assertContainerBuilderHasParameter(
            'ez_io.metadata_handlers',
            ['my_metadata_handler' => ['name' => 'my_metadata_handler', 'type' => 'flysystem', 'adapter' => 'my_adapter']]
        );
    }

    public function testParametersWithBinarydataHandler()
    {
        $config = [
            'binarydata_handlers' => [
                'my_binarydata_handler' => ['flysystem' => ['adapter' => 'my_adapter']],
            ],
        ];
        $this->load($config);

        $this->assertContainerBuilderHasParameter('ez_io.metadata_handlers', []);
        $this->assertContainerBuilderHasParameter(
            'ez_io.binarydata_handlers',
            ['my_binarydata_handler' => ['name' => 'my_binarydata_handler', 'type' => 'flysystem', 'adapter' => 'my_adapter']]
        );
    }

    public function testUrlPrefixConfigurationIsUsedToDecorateUrl(): void
    {
        $this->container->registerExtension(
            new EzPublishCoreExtension(
                [
                    new Parser\IO(new ComplexSettingParser()),
                ]
            )
        );
        $this->container->prependExtensionConfig(
            'ezpublish',
            Yaml::parseFile(self::FIXTURES_DIR . '/url_prefix_test_config.yaml')['ezplatform']
        );
        $this->buildMinimalContainerForUrlPrefixTest();

        $decorator = $this->container->get('ezpublish.core.io.prefix_url_decorator');

        self::assertEquals(
            'http://static.example.com/my/image.png',
            $decorator->decorate('my/image.png')
        );
    }

    private function buildMinimalContainerForUrlPrefixTest(): void
    {
        // unrelated, but needed Container configuration
        $this->container->setParameter('kernel.environment', 'dev');
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.project_dir', self::FIXTURES_DIR);
        $this->container->setParameter('kernel.cache_dir', self::FIXTURES_DIR . '/cache');

        $this->container->addCompilerPass(new ChainConfigResolverPass());
        $this->container->addCompilerPass(new SetAllServicesPublicPass());

        $this->container->compile();
    }
}
