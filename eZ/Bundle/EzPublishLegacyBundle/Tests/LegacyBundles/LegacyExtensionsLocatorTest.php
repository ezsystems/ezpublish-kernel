<?php
/**
 * File containing the LegacyExtensionsLocatorTest class.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Tests\LegacyBundles;

use eZ\Bundle\EzPublishLegacyBundle\LegacyBundles\LegacyExtensionsLocator;
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

    public function testLocate()
    {
        $locator = new LegacyExtensionsLocator( $this->vfsRoot );

        self::assertEquals(
            array(
                vfsStream::url( 'eZ/TestBundle/ezpublish_legacy/extension1' ),
                vfsStream::url( 'eZ/TestBundle/ezpublish_legacy/extension2' )
            ),
            $locator->locate( vfsStream::url( 'eZ/TestBundle/' ) )
        );
    }

    public function testLocateNoLegacy()
    {
        $locator = new LegacyExtensionsLocator( $this->vfsRoot );

        self::assertEquals(
            array(),
            $locator->locate( vfsStream::url( 'No/Such/Bundle/' ) )
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
