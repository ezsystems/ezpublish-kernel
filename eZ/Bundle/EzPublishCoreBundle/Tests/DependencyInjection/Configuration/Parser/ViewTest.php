<?php
/**
 * File containing the ViewTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\BlockView;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\ContentView;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\LocationView;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Symfony\Component\Yaml\Yaml;

class ViewTest extends AbstractParserTestCase
{
    private $config;

    protected function getContainerExtensions()
    {
        return array(
            new EzPublishCoreExtension( array( new LocationView(), new ContentView(), new BlockView() ) )
        );
    }

    protected function getMinimalConfiguration()
    {
        return $this->config = Yaml::parse( file_get_contents( __DIR__ . '/../../Fixtures/ezpublish_view.yml' ) );
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

        $this->assertConfigResolverParameterValue( 'location_view', $expectedLocationView, 'ezdemo_site', false );
        $this->assertConfigResolverParameterValue( 'location_view', $expectedLocationView, 'fre', false );
        $this->assertConfigResolverParameterValue( 'location_view', array(), 'ezdemo_site_admin', false );
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

        $this->assertConfigResolverParameterValue( 'content_view', $expectedContentView, 'ezdemo_site', false );
        $this->assertConfigResolverParameterValue( 'content_view', $expectedContentView, 'fre', false );
        $this->assertConfigResolverParameterValue( 'content_view', array(), 'ezdemo_site_admin', false );
    }

    public function testBlockView()
    {
        $this->load();
        $this->assertConfigResolverParameterValue(
            'block_view',
            array( 'block' => $this->config['system']['ezdemo_frontend_group']['block_view'] ),
            'ezdemo_site',
            false
        );
        $this->assertConfigResolverParameterValue(
            'block_view',
            array( 'block' => $this->config['system']['ezdemo_frontend_group']['block_view'] ),
            'fre',
            false
        );
        $this->assertConfigResolverParameterValue(
            'block_view',
            array(),
            'ezdemo_site_admin',
            false
        );
    }
}
