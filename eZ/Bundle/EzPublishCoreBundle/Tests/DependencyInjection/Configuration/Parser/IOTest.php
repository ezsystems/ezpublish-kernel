<?php

/**
 * File containing the IOTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\IO;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Symfony\Component\Yaml\Yaml;

class IOTest extends AbstractParserTestCase
{
    private $minimalConfig;

    public function setUp()
    {
        parent::setUp();
        $this->container->setParameter('ezsettings.default.var_dir', 'var');
        $this->container->setParameter('ezsettings.default.storage_dir', 'storage');
        $this->container->setParameter('ezsettings.ezdemo_site.var_dir', 'var/ezdemo_site');
    }

    protected function getContainerExtensions()
    {
        return array(
            new EzPublishCoreExtension(array(new IO(new ComplexSettingParser()))),
        );
    }

    protected function getMinimalConfiguration()
    {
        return $this->minimalConfig = Yaml::parse(file_get_contents(__DIR__ . '/../../Fixtures/ezpublish_minimal.yml'));
    }

    public function testHandlersConfig()
    {
        $config = array(
            'system' => array(
                'ezdemo_site' => array(
                    'io' => array(
                        'binarydata_handler' => 'cluster',
                        'metadata_handler' => 'cluster',
                    ),
                ),
            ),
        );

        $this->load($config);

        $this->assertConfigResolverParameterValue('io.metadata_handler', 'cluster', 'ezdemo_site');
        $this->assertConfigResolverParameterValue('io.binarydata_handler', 'cluster', 'ezdemo_site');
    }

    /**
     * Tests that a complex default io.url_prefix will be set in a context where one of its dependencies is set.
     */
    public function testComplexIoUrlPrefix()
    {
        $this->load();

        // Should have been defined & converted in ezdemo_site
        $this->assertContainerBuilderHasParameter('ezsettings.ezdemo_site.io.url_prefix', 'var/ezdemo_site/storage');
        // Should have been converted in default
        $this->assertContainerBuilderHasParameter('ezsettings.default.io.url_prefix', 'var/storage');
    }

    /**
     * Tests that a complex default io.url_prefix will be set in a context where one of its dependencies is set.
     */
    public function testComplexIoLegacyUrlPrefix()
    {
        $this->container->setParameter('ezsettings.default.var_dir', 'var');
        $this->container->setParameter('ezsettings.default.storage_dir', 'storage');
        $this->container->setParameter('ezsettings.ezdemo_site.var_dir', 'var/ezdemo_site');

        $this->load();

        // Should have been defined & converted in ezdemo_site
        $this->assertContainerBuilderHasParameter('ezsettings.ezdemo_site.io.legacy_url_prefix', 'var/ezdemo_site/storage');
        // Should have been converted in default
        $this->assertContainerBuilderHasParameter('ezsettings.default.io.legacy_url_prefix', 'var/storage');
    }

    /**
     * Tests that a complex default io.url_prefix will be set in a context where one of its dependencies is set.
     */
    public function testComplexIoRootDir()
    {
        $this->container->setParameter('ezsettings.default.var_dir', 'var');
        $this->container->setParameter('ezsettings.default.storage_dir', 'storage');
        $this->container->setParameter('ezsettings.ezdemo_site.var_dir', 'var/ezdemo_site');

        $this->load();

        // Should have been defined & converted in ezdemo_site
        $this->assertContainerBuilderHasParameter('ezsettings.ezdemo_site.io.root_dir', '%webroot_dir%/var/ezdemo_site/storage');
        // Should have been converted in default
        $this->assertContainerBuilderHasParameter('ezsettings.default.io.root_dir', '%webroot_dir%/var/storage');
    }
}
