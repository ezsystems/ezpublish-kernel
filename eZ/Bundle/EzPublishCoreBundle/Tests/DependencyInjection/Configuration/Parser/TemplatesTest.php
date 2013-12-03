<?php
/**
 * File containing the TemplatesTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\FieldDefinitionSettingsTemplates;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\FieldTemplates;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Yaml\Yaml;

class TemplatesTest extends AbstractExtensionTestCase
{
    private $config;

    /**
     * Return an array of container extensions you need to be registered for each test (usually just the container
     * extension you are testing.
     *
     * @return ExtensionInterface[]
     */
    protected function getContainerExtensions()
    {
        return array(
            new EzPublishCoreExtension(
                array( new FieldTemplates(), new FieldDefinitionSettingsTemplates() )
            )
        );
    }

    protected function getMinimalConfiguration()
    {
        return $this->config = Yaml::parse( __DIR__ . '/../../Fixtures/ezpublish_templates.yml' );
    }

    public function testFieldTemplates()
    {
        $this->load();
        $fixedUpConfig = $this->getExpectedConfigFieldTemplates( $this->config );
        $groupFieldTemplates = $fixedUpConfig['system']['ezdemo_frontend_group']['field_templates'];
        $demoSiteFieldTemplates = $fixedUpConfig['system']['ezdemo_site']['field_templates'];
        $this->assertEquals(
            array_merge(
                // Adding default kernel value.
                array( array( 'template' => 'EzPublishCoreBundle::content_fields.html.twig', 'priority' => 0 ) ),
                $groupFieldTemplates,
                $demoSiteFieldTemplates
            ),
            $this->container->getParameter( 'ezsettings.ezdemo_site.field_templates' )
        );
        $this->assertEquals(
            array_merge(
                // Adding default kernel value.
                array( array( 'template' => 'EzPublishCoreBundle::content_fields.html.twig', 'priority' => 0 ) ),
                $groupFieldTemplates
            ),
            $this->container->getParameter( 'ezsettings.fre.field_templates' )
        );
        $this->assertEquals(
            array( array( 'template' => 'EzPublishCoreBundle::content_fields.html.twig', 'priority' => 0 ) ),
            $this->container->getParameter( 'ezsettings.ezdemo_site_admin.field_templates' )
        );
    }

    /**
     * Fixes up input configuration for field_templates as semantic configuration parser does, adding a default priority of 0 when not set.
     *
     * @param array $config
     *
     * @return array
     */
    private function getExpectedConfigFieldTemplates( array $config )
    {
        foreach ( $config['system']['ezdemo_frontend_group']['field_templates'] as &$block )
        {
            if ( !isset( $block['priority'] ) )
            {
                $block['priority'] = 0;
            }
        }

        return $config;
    }

    public function testFieldDefinitionSettingsTemplates()
    {
        $this->load();
        $fixedUpConfig = $this->getExpectedConfigFieldDefinitionSettingsTemplates( $this->config );
        $groupFieldTemplates = $fixedUpConfig['system']['ezdemo_frontend_group']['fielddefinition_settings_templates'];
        $demoSiteFieldTemplates = $fixedUpConfig['system']['ezdemo_site']['fielddefinition_settings_templates'];
        $this->assertEquals(
            array_merge(
                // Adding default kernel value.
                array( array( 'template' => 'EzPublishCoreBundle::fielddefinition_settings.html.twig', 'priority' => 0 ) ),
                $groupFieldTemplates,
                $demoSiteFieldTemplates
            ),
            $this->container->getParameter( 'ezsettings.ezdemo_site.fielddefinition_settings_templates' )
        );
        $this->assertEquals(
            array_merge(
                // Adding default kernel value.
                array( array( 'template' => 'EzPublishCoreBundle::fielddefinition_settings.html.twig', 'priority' => 0 ) ),
                $groupFieldTemplates
            ),
            $this->container->getParameter( 'ezsettings.fre.fielddefinition_settings_templates' )
        );
        $this->assertEquals(
            array( array( 'template' => 'EzPublishCoreBundle::fielddefinition_settings.html.twig', 'priority' => 0 ) ),
            $this->container->getParameter( 'ezsettings.ezdemo_site_admin.fielddefinition_settings_templates' )
        );
    }

    /**
     * Fixes up input configuration for fielddefinition_settings_templates as semantic configuration parser does, adding a default priority of 0 when not set.
     *
     * @param array $config
     *
     * @return array
     */
    private function getExpectedConfigFieldDefinitionSettingsTemplates( array $config )
    {
        foreach ( $config['system']['ezdemo_frontend_group']['fielddefinition_settings_templates'] as &$block )
        {
            if ( !isset( $block['priority'] ) )
            {
                $block['priority'] = 0;
            }
        }

        return $config;
    }
}
