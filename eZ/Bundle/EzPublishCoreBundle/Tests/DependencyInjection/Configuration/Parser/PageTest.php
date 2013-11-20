<?php
/**
 * File containing the PageTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Page;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Yaml\Yaml;

class PageTest extends AbstractExtensionTestCase
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
            new EzPublishCoreExtension( array( new Page() ) )
        );
    }

    protected function getMinimalConfiguration()
    {
        return $this->config = Yaml::parse( __DIR__ . '/../../Fixtures/ezpublish_page.yml' );
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
        $this->assertSame( $pageConfigForSiteaccess, $this->container->getParameter( 'ezsettings.ezdemo_site.ezpage' ) );
        $this->assertSame( $pageConfigForSiteaccess, $this->container->getParameter( 'ezsettings.fre.ezpage' ) );
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
        $this->assertSame( $expected, $this->container->getParameter( 'ezsettings.fre.ezpage' ) );
    }

    /**
     * Returs expected ezpage configuration for a siteaccess, where only enabled blocks/layouts should be present.
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
