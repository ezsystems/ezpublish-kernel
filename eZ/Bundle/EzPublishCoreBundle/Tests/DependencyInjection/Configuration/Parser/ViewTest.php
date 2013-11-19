<?php
/**
 * File containing the ViewTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\BlockView;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\ContentView;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\LocationView;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Yaml\Yaml;

class ViewTest extends AbstractExtensionTestCase
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
            new EzPublishCoreExtension( array( new LocationView(), new ContentView(), new BlockView() ) )
        );
    }

    protected function getMinimalConfiguration()
    {
        return $this->config = Yaml::parse( __DIR__ . '/../../Fixtures/ezpublish_view.yml' );
    }

    public function testLocationView()
    {
        $this->load();
        $expectedLocationView = $this->config['system']['ezdemo_frontend_group']['location_view'];
        foreach ( $expectedLocationView as &$rulesets )
        {
            foreach ( $rulesets as &$config )
            {
                if ( !isset( $config['params'] ) )
                {
                    $config['params'] = array();
                }
            }
        }
        $this->assertEquals(
            $expectedLocationView,
            $this->container->getParameter( 'ezsettings.ezdemo_site.location_view' )
        );
        $this->assertEquals(
            $expectedLocationView,
            $this->container->getParameter( 'ezsettings.fre.location_view' )
        );
        $this->assertSame( array(), $this->container->getParameter( 'ezsettings.ezdemo_site_admin.location_view' ) );
    }

    public function testContentView()
    {
        $this->load();
        $expectedContentView = $this->config['system']['ezdemo_frontend_group']['content_view'];
        foreach ( $expectedContentView as &$rulesets )
        {
            foreach ( $rulesets as &$config )
            {
                if ( !isset( $config['params'] ) )
                {
                    $config['params'] = array();
                }
            }
        }
        $this->assertEquals(
            $expectedContentView,
            $this->container->getParameter( 'ezsettings.ezdemo_site.content_view' )
        );
        $this->assertEquals(
            $expectedContentView,
            $this->container->getParameter( 'ezsettings.fre.content_view' )
        );
        $this->assertSame( array(), $this->container->getParameter( 'ezsettings.ezdemo_site_admin.location_view' ) );
    }

    public function testBlockView()
    {
        $this->load();
        $this->assertEquals(
            array( 'block' => $this->config['system']['ezdemo_frontend_group']['block_view'] ),
            $this->container->getParameter( 'ezsettings.ezdemo_site.block_view' )
        );
        $this->assertEquals(
            array( 'block' => $this->config['system']['ezdemo_frontend_group']['block_view'] ),
            $this->container->getParameter( 'ezsettings.fre.block_view' )
        );
        $this->assertSame( array(), $this->container->getParameter( 'ezsettings.ezdemo_site_admin.block_view' ) );
    }
}
