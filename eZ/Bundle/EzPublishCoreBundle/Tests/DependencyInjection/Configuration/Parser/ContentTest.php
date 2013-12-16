<?php
/**
 * File containing the ContentTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Content as ContentConfigParser;
use Symfony\Component\Yaml\Yaml;

class ContentTest extends AbstractExtensionTestCase
{
    /**
     * Return an array of container extensions you need to be registered for each test (usually just the container
     * extension you are testing.
     *
     * @return ExtensionInterface[]
     */
    protected function getContainerExtensions()
    {
        return array(
            new EzPublishCoreExtension( array( new ContentConfigParser ) )
        );
    }

    protected function getMinimalConfiguration()
    {
        return Yaml::parse( __DIR__ . '/../../Fixtures/ezpublish_minimal.yml' );
    }

    public function testDefaultContentSettings()
    {
        $this->load();

        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.content.view_cache' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.content.ttl_cache' ) );
        $this->assertFalse( $this->container->hasParameter( 'ezsettings.ezdemo_site.content.default_ttl' ) );
    }

    /**
     * @dataProvider contentSettingsProvider
     */
    public function testContentSettings( array $config, array $expected )
    {
        $this->load(
            array(
                'system' => array(
                    'ezdemo_site' => $config
                )
            )
        );

        foreach ( $expected as $key => $val )
        {
            $this->assertSame( $val, $this->container->getParameter( $key ) );
        }
    }

    public function contentSettingsProvider()
    {
        return array(
            array(
                array(
                    'content' => array(
                        'view_cache' => true,
                        'ttl_cache' => true,
                        'default_ttl' => 100,
                    )
                ),
                array(
                    'ezsettings.ezdemo_site.content.view_cache' => true,
                    'ezsettings.ezdemo_site.content.ttl_cache' => true,
                    'ezsettings.ezdemo_site.content.default_ttl' => 100,
                )
            ),
            array(
                array(
                    'content' => array(
                        'view_cache' => false,
                        'ttl_cache' => false,
                        'default_ttl' => 123,
                    )
                ),
                array(
                    'ezsettings.ezdemo_site.content.view_cache' => false,
                    'ezsettings.ezdemo_site.content.ttl_cache' => false,
                    'ezsettings.ezdemo_site.content.default_ttl' => 123,
                )
            ),
            array(
                array(
                    'content' => array(
                        'view_cache' => false,
                    )
                ),
                array(
                    'ezsettings.ezdemo_site.content.view_cache' => false,
                    'ezsettings.ezdemo_site.content.ttl_cache' => true,
                    'ezsettings.ezdemo_site.content.default_ttl' => 60,
                )
            ),
            array(
                array(
                    'content' => array(
                        'tree_root' => array( 'location_id' => 123 ),
                    )
                ),
                array(
                    'ezsettings.ezdemo_site.content.view_cache' => true,
                    'ezsettings.ezdemo_site.content.ttl_cache' => true,
                    'ezsettings.ezdemo_site.content.default_ttl' => 60,
                    'ezsettings.ezdemo_site.content.tree_root.location_id' => 123,
                )
            ),
            array(
                array(
                    'content' => array(
                        'tree_root' => array(
                            'location_id' => 456,
                            'excluded_uri_prefixes' => array( '/media/images', '/products' )
                        ),
                    )
                ),
                array(
                    'ezsettings.ezdemo_site.content.view_cache' => true,
                    'ezsettings.ezdemo_site.content.ttl_cache' => true,
                    'ezsettings.ezdemo_site.content.default_ttl' => 60,
                    'ezsettings.ezdemo_site.content.tree_root.location_id' => 456,
                    'ezsettings.ezdemo_site.content.tree_root.excluded_uri_prefixes' => array( '/media/images', '/products' ),
                )
            ),
            array(
                array(
                    'content' => array(),
                    'fieldtypes' => array(
                        'ezxml' => array(
                            'custom_tags' => array(
                                array( 'path' => '/foo/bar.xsl', 'priority' => 123 ),
                                array( 'path' => '/foo/custom.xsl', 'priority' => -10 ),
                                array( 'path' => '/another/custom.xsl', 'priority' => 27 ),
                            )
                        )
                    )
                ),
                array(
                    'ezsettings.ezdemo_site.content.view_cache' => true,
                    'ezsettings.ezdemo_site.content.ttl_cache' => true,
                    'ezsettings.ezdemo_site.content.default_ttl' => 60,
                    'ezsettings.ezdemo_site.fieldtypes.ezxml.custom_xsl' => array(
                        // Default settings will be added
                        array( 'path' => '%kernel.root_dir%/../vendor/ezsystems/ezpublish-kernel/eZ/Publish/Core/FieldType/XmlText/Input/Resources/stylesheets/eZXml2Html5_core.xsl', 'priority' => 0 ),
                        array( 'path' => '%kernel.root_dir%/../vendor/ezsystems/ezpublish-kernel/eZ/Publish/Core/FieldType/XmlText/Input/Resources/stylesheets/eZXml2Html5_custom.xsl', 'priority' => 0 ),
                        array( 'path' => '/foo/bar.xsl', 'priority' => 123 ),
                        array( 'path' => '/foo/custom.xsl', 'priority' => -10 ),
                        array( 'path' => '/another/custom.xsl', 'priority' => 27 ),
                    ),
                )
            ),
        );
    }
}
