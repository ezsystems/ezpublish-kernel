<?php
/**
 * File containing the ImageTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Image;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Yaml\Yaml;

class ImageTest extends AbstractExtensionTestCase
{
    private $config;

    protected function setUp()
    {
        parent::setUp();

        if ( !isset( $_ENV['imagemagickConvertPath'] ) || !is_executable( $_ENV['imagemagickConvertPath'] ) )
        {
            $this->markTestSkipped( 'Missing or mis-configured Imagemagick convert path.' );
        }
    }

    protected function getMinimalConfiguration()
    {
        $this->config = Yaml::parse( __DIR__ . '/../../Fixtures/ezpublish_image.yml' );
        $this->config += array(
            'imagemagick' => array(
                'enabled' => true,
                'path' => $_ENV['imagemagickConvertPath']
            )
        );

        return $this->config;
    }

    /**
     * Return an array of container extensions you need to be registered for each test (usually just the container
     * extension you are testing.
     *
     * @return ExtensionInterface[]
     */
    protected function getContainerExtensions()
    {
        return array(
            new EzPublishCoreExtension( array( new Image() ) )
        );
    }

    public function testVariations()
    {
        $this->load();

        $expected = $this->config['system']['ezdemo_group']['image_variations'] + $this->container->getParameter( 'ezsettings.default.image_variations' );
        $this->assertEquals( $expected, $this->container->getParameter( 'ezsettings.ezdemo_site.image_variations' ) );
        $this->assertEquals( $expected, $this->container->getParameter( 'ezsettings.ezdemo_site_admin.image_variations' ) );
        $this->assertEquals(
            $expected + $this->config['system']['fre']['image_variations'],
            $this->container->getParameter( 'ezsettings.fre.image_variations' )
        );
    }

    /**
     * @dataProvider prePostParametersProvider
     */
    public function testPrePostParameters( array $config, array $expected )
    {
        $this->load( array( 'system' => $config ) );
        foreach ( $expected as $name => $value )
        {
            if ( $value === null )
            {
                $this->assertFalse( $this->container->hasParameter( $name ) );
            }
            else
            {
                $this->assertSame( $value, $this->container->getParameter( $name ) );
            }
        }
    }

    public function prePostParametersProvider()
    {
        return array(
            array(
                array(
                    'ezdemo_site' => array(
                        'imagemagick' => array(
                            'pre_parameters' => '-foo -bar',
                            'post_parameters' => '-baz'
                        )
                    )
                ),
                array(
                    'ezsettings.ezdemo_site.imagemagick.pre_parameters' => '-foo -bar',
                    'ezsettings.ezdemo_site.imagemagick.post_parameters' => '-baz',
                    'ezsettings.fre.imagemagick.pre_parameters' => null,
                    'ezsettings.fre.imagemagick.post_parameters' => null,
                )
            ),
            array(
                array(
                    'ezdemo_site' => array(
                        'imagemagick' => array(
                            'pre_parameters' => '-foo -bar',
                        )
                    )
                ),
                array(
                    'ezsettings.ezdemo_site.imagemagick.pre_parameters' => '-foo -bar',
                    'ezsettings.ezdemo_site.imagemagick.post_parameters' => null,
                    'ezsettings.fre.imagemagick.pre_parameters' => null,
                    'ezsettings.fre.imagemagick.post_parameters' => null,
                )
            ),
            array(
                array(
                    'ezdemo_site' => array(
                        'imagemagick' => array(
                            'post_parameters' => '-baz'
                        )
                    )
                ),
                array(
                    'ezsettings.ezdemo_site.imagemagick.pre_parameters' => null,
                    'ezsettings.ezdemo_site.imagemagick.post_parameters' => '-baz',
                    'ezsettings.fre.imagemagick.pre_parameters' => null,
                    'ezsettings.fre.imagemagick.post_parameters' => null,
                )
            ),
            array(
                array(
                    'fre' => array(
                        'imagemagick' => array(
                            'pre_parameters' => '-fre',
                            'post_parameters' => '-baz -fre'
                        )
                    )
                ),
                array(
                    'ezsettings.ezdemo_site.imagemagick.pre_parameters' => null,
                    'ezsettings.ezdemo_site.imagemagick.post_parameters' => null,
                    'ezsettings.fre.imagemagick.pre_parameters' => '-fre',
                    'ezsettings.fre.imagemagick.post_parameters' => '-baz -fre',
                )
            ),
        );
    }
}
