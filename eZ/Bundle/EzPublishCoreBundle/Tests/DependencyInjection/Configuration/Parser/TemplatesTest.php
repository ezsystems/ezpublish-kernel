<?php

/**
 * File containing the TemplatesTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\FieldDefinitionSettingsTemplates;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\FieldTemplates;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Provider\StaticSiteAccessProvider;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface;
use Symfony\Component\Yaml\Yaml;

class TemplatesTest extends AbstractParserTestCase
{
    private $config;

    protected function getContainerExtensions(): array
    {
        return [
            new EzPublishCoreExtension(
                [new FieldTemplates(), new FieldDefinitionSettingsTemplates()]
            ),
        ];
    }

    protected function getMinimalConfiguration(): array
    {
        return $this->config = Yaml::parse(file_get_contents(__DIR__ . '/../../Fixtures/ezpublish_templates.yml'));
    }

    public function testFieldTemplates()
    {
        $this->load();
        $fixedUpConfig = $this->getExpectedConfigFieldTemplates($this->config);
        $groupFieldTemplates = $fixedUpConfig['system']['ezdemo_frontend_group']['field_templates'];
        $demoSiteFieldTemplates = $fixedUpConfig['system']['ezdemo_site']['field_templates'];
        $this->assertConfigResolverParameterValue(
            'field_templates',
            array_merge(
                // Adding default kernel value.
                [['template' => '%ezplatform.default_templates.field_templates%', 'priority' => 0]],
                $groupFieldTemplates,
                $demoSiteFieldTemplates
            ),
            'ezdemo_site',
            false
        );
        $this->assertConfigResolverParameterValue(
            'field_templates',
            array_merge(
                // Adding default kernel value.
                [['template' => '%ezplatform.default_templates.field_templates%', 'priority' => 0]],
                $groupFieldTemplates
            ),
            'fre',
            false
        );
        $this->assertConfigResolverParameterValue(
            'field_templates',
            [['template' => '%ezplatform.default_templates.field_templates%', 'priority' => 0]],
            'ezdemo_site_admin',
            false
        );
    }

    protected function getSiteAccessProviderMock(): SiteAccessProviderInterface
    {
        $siteAccessProvider = $this->createMock(SiteAccessProviderInterface::class);
        $siteAccessProvider
            ->method('isDefined')
            ->willReturnMap([
                ['ezdemo_site', true],
                ['fre', true],
                ['ezdemo_site_admin', true],
            ]);
        $siteAccessProvider
            ->method('getSiteAccess')
            ->willReturnMap([
                ['ezdemo_site', $this->getSiteAccess('ezdemo_site', StaticSiteAccessProvider::class, ['ezdemo_group', 'ezdemo_frontend_group'])],
                ['fre', $this->getSiteAccess('fre', StaticSiteAccessProvider::class, ['ezdemo_group', 'ezdemo_frontend_group'])],
                ['ezdemo_site_admin', $this->getSiteAccess('ezdemo_site_admin', StaticSiteAccessProvider::class, ['ezdemo_group'])],
            ]);

        return $siteAccessProvider;
    }

    /**
     * Fixes up input configuration for field_templates as semantic configuration parser does, adding a default priority of 0 when not set.
     *
     * @param array $config
     *
     * @return array
     */
    private function getExpectedConfigFieldTemplates(array $config)
    {
        foreach ($config['system']['ezdemo_frontend_group']['field_templates'] as &$block) {
            if (!isset($block['priority'])) {
                $block['priority'] = 0;
            }
        }

        return $config;
    }

    public function testFieldDefinitionSettingsTemplates()
    {
        $this->load();
        $fixedUpConfig = $this->getExpectedConfigFieldDefinitionSettingsTemplates($this->config);
        $groupFieldTemplates = $fixedUpConfig['system']['ezdemo_frontend_group']['fielddefinition_settings_templates'];
        $demoSiteFieldTemplates = $fixedUpConfig['system']['ezdemo_site']['fielddefinition_settings_templates'];

        $this->assertConfigResolverParameterValue(
            'fielddefinition_settings_templates',
            array_merge(
                // Adding default kernel value.
                [['template' => '%ezplatform.default_templates.fielddefinition_settings_templates%', 'priority' => 0]],
                $groupFieldTemplates,
                $demoSiteFieldTemplates
            ),
            'ezdemo_site',
            false
        );
        $this->assertConfigResolverParameterValue(
            'fielddefinition_settings_templates',
            array_merge(
                // Adding default kernel value.
                [['template' => '%ezplatform.default_templates.fielddefinition_settings_templates%', 'priority' => 0]],
                $groupFieldTemplates
            ),
            'fre',
            false
        );
        $this->assertConfigResolverParameterValue(
            'fielddefinition_settings_templates',
            [['template' => '%ezplatform.default_templates.fielddefinition_settings_templates%', 'priority' => 0]],
            'ezdemo_site_admin',
            false
        );
    }

    /**
     * Fixes up input configuration for fielddefinition_settings_templates as semantic configuration parser does, adding a default priority of 0 when not set.
     *
     * @param array $config
     *
     * @return array
     */
    private function getExpectedConfigFieldDefinitionSettingsTemplates(array $config)
    {
        foreach ($config['system']['ezdemo_frontend_group']['fielddefinition_settings_templates'] as &$block) {
            if (!isset($block['priority'])) {
                $block['priority'] = 0;
            }
        }

        return $config;
    }
}
