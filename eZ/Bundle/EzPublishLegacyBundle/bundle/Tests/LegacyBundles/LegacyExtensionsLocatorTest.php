<?php
/**
 * File containing the LegacyExtensionsLocatorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Tests\LegacyBundles;

use eZ\Bundle\EzPublishLegacyBundle\LegacyBundles\LegacyExtensionsLocator;
use Mockery;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use PHPUnit_Framework_TestCase;

class LegacyExtensionsLocatorTest extends PHPUnit_Framework_TestCase
{
    /** @var \org\bovigo\vfs\vfsStreamDirectory */
    private $vfsRoot;

    public function setUp()
    {
        $this->initVfs();
    }

    public function testGetExtensionDirectories()
    {
        $locator = new LegacyExtensionsLocator( $this->vfsRoot );

        self::assertEquals(
            array(
                vfsStream::url( 'eZ/TestBundle/ezpublish_legacy/extension1' ),
                vfsStream::url( 'eZ/TestBundle/ezpublish_legacy/extension2' )
            ),
            $locator->getExtensionDirectories( vfsStream::url( 'eZ/TestBundle/' ) )
        );
    }

    public function testGetExtensionDirectoriesNoLegacy()
    {
        $locator = new LegacyExtensionsLocator( $this->vfsRoot );

        self::assertEquals(
            array(),
            $locator->getExtensionDirectories( vfsStream::url( 'No/Such/Bundle/' ) )
        );
    }

    public function testGetExtensionsNames()
    {
        $bundle = Mockery::mock(
            'eZ\Bundle\EzPublishLegacyBundle\LegacyBundles\LegacyBundleInterface,' .
            'Symfony\Component\HttpKernel\Bundle\BundleInterface'
        );
        $bundle
            ->shouldReceive( 'getPath' )
            ->andReturn( vfsStream::url( 'eZ/TestBundle/' ) );
        $bundle
            ->shouldReceive( 'getLegacyExtensionsNames' )
            ->andReturn( array( 'extension3' ) );

        $locator = new LegacyExtensionsLocator( $this->vfsRoot );

        self::assertEquals(
            array(
                'extension1',
                'extension2',
                'extension3'
            ),
            $locator->getExtensionNames( $bundle )
        );
    }

    protected function initVfs()
    {
        $structure = array(
            'eZ' => array(
                'TestBundle' => array(
                    'ezpublish_legacy' => array(
                        'extension1' => array( 'extension.xml' => '' ),
                        'extension2' => array( 'extension.xml' => '' ),
                        'not_extension' => array()
                    ),
                    'Resources' => array( 'config' => array() )
                )
            ),
        );
        $this->vfsRoot = vfsStream::setup( '_root_', null, $structure );
    }
}
