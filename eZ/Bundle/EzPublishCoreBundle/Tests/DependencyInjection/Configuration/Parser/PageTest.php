<?php
/**
 * File containing the PageTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Page;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Symfony\Component\Yaml\Yaml;

class PageTest extends AbstractParserTestCase
{
    private $config;

    protected function getContainerExtensions()
    {
        return array(
            new EzPublishCoreExtension( array( new Page() ) )
        );
    }

    protected function getMinimalConfiguration()
    {
        return $this->config = Yaml::parse( file_get_contents( __DIR__ . '/../../Fixtures/ezpublish_page.yml' ) );
    }

    public function testDefaultPageConfig()
    {
        $this->load();

        $defaultConfig = array(
            'layouts' => $this->container->getParameter( 'ezpublish.ezpage.layouts' ),
            'blocks' => $this->container->getParameter( 'ezpublish.ezpage.blocks' ),
            'enabledLayouts' => $this->container->getParameter( 'ezpublish.ezpage.enabledLayouts' ),
            'enabledBlocks' => $this->container->getParameter( 'ezpublish.ezpage.enabledBlocks' ),
        );
        $this->assertSame( $defaultConfig, $this->container->getParameter( 'ezsettings.default.ezpage' ) );

        // For each siteaccess we expect to only have enabled layout/blocks
        $pageConfigForSiteaccess = $this->getPageConfigForSiteaccessFromDefaults( $defaultConfig );
        $this->assertConfigResolverParameterValue( 'ezpage', $pageConfigForSiteaccess, 'ezdemo_site' );
        $this->assertConfigResolverParameterValue( 'ezpage', $pageConfigForSiteaccess, 'fre' );
    }

    public function testSiteaccessPageConfig()
    {
        $this->load();
        $defaultConfig = array(
            'layouts' => $this->container->getParameter( 'ezpublish.ezpage.layouts' ),
            'blocks' => $this->container->getParameter( 'ezpublish.ezpage.blocks' ),
            'enabledLayouts' => $this->container->getParameter( 'ezpublish.ezpage.enabledLayouts' ),
            'enabledBlocks' => $this->container->getParameter( 'ezpublish.ezpage.enabledBlocks' ),
        );

        $customLayouts = array(
            'FoobarLayout2' => array( 'name' => 'Foo layout 2', 'template' => 'foolayout2.html.twig' )
        );
        $enabledLayouts = array( 'FoobarLayout2', 'GlobalZoneLayout' );
        $customBlocks = array(
            'FoobarBlock2' => array( 'name' => 'Foo block 2' )
        );
        $enabledBlocks = array( 'FoobarBlock2', 'DemoBlock' );
        $siteaccessConfig = array(
            'layouts' => $customLayouts,
            'blocks' => $customBlocks,
            'enabledLayouts' => $enabledLayouts,
            'enabledBlocks' => $enabledBlocks
        );
        $this->load(
            array(
                'system' => array(
                    'fre' => array( 'ezpage' => $siteaccessConfig )
                )
            )
        );

        $expected = $this->getPageConfigForSiteaccessFromDefaults( $defaultConfig, $siteaccessConfig );
        $this->assertConfigResolverParameterValue( 'ezpage', $expected, 'fre' );
    }

    /**
     * Returns expected ezpage configuration for a siteaccess, where only enabled blocks/layouts should be present.
     *
     * @param array $defaultConfig
     * @param array $additionalConfig
     *
     * @return array
     */
    private function getPageConfigForSiteaccessFromDefaults( array $defaultConfig, array $additionalConfig = array() )
    {
        $pageConfigForSiteaccess = array(
            'layouts' => array(),
            'blocks' => array(),
            'enabledLayouts' => $defaultConfig['enabledLayouts'],
            'enabledBlocks' => $defaultConfig['enabledBlocks']
        );

        // Default settings
        foreach ( $defaultConfig['enabledLayouts'] as $enabledLayout )
        {
            $pageConfigForSiteaccess['layouts'][$enabledLayout] = $defaultConfig['layouts'][$enabledLayout];
        }
        foreach ( $defaultConfig['enabledBlocks'] as $enabledBlock )
        {
            $pageConfigForSiteaccess['blocks'][$enabledBlock] = $defaultConfig['blocks'][$enabledBlock];
        }

        // Siteaccess settings
        if ( !empty( $additionalConfig ) )
        {
            foreach ( $additionalConfig['enabledLayouts'] as $enabledLayout )
            {
                if ( isset( $additionalConfig['layouts'][$enabledLayout] ) )
                {
                    $pageConfigForSiteaccess['layouts'][$enabledLayout] = $additionalConfig['layouts'][$enabledLayout];
                    $pageConfigForSiteaccess['enabledLayouts'][] = $enabledLayout;
                }
            }

            foreach ( $additionalConfig['enabledBlocks'] as $enabledBlock )
            {
                if ( isset( $additionalConfig['blocks'][$enabledBlock] ) )
                {
                    $pageConfigForSiteaccess['blocks'][$enabledBlock] = $additionalConfig['blocks'][$enabledBlock];
                    $pageConfigForSiteaccess['enabledBlocks'][] = $enabledBlock;
                }
            }
        }

        $pageConfigForSiteaccess['enabledBlocks'] = array_unique( $pageConfigForSiteaccess['enabledBlocks'] );
        $pageConfigForSiteaccess['enabledLayouts'] = array_unique( $pageConfigForSiteaccess['enabledLayouts'] );
        return $pageConfigForSiteaccess;
    }
}
