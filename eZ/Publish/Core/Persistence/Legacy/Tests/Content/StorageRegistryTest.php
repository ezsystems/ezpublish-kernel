<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\StorageRegistryTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry,
    eZ\Publish\SPI\FieldType\FieldStorage;

/**
 * Test case for StorageRegistry
 */
class StorageRegistryTest extends TestCase
{
    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry::register
     */
    public function testRegister()
    {
        $registry = new StorageRegistry();

        $storage = $this->getStorageMock();

        $registry->register( 'some-type', $storage );

        $this->assertAttributeSame(
            array(
                'some-type' => $storage,
            ),
            'storageMap',
            $registry
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry::getStorage
     */
    public function testGetStorage()
    {
        $registry = new StorageRegistry();

        $storage = $this->getStorageMock();
        $registry->register( 'some-type', $storage );

        $res = $registry->getStorage( 'some-type' );

        $this->assertSame(
            $storage,
            $res
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry::getStorage
     * @covers eZ\Publish\Core\Persistence\Legacy\Exception\StorageNotFound
     */
    public function testGetNotFound()
    {
        $registry = new StorageRegistry();
        self::assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
            $registry->getStorage( 'not-found' )
        );
    }

    /**
     * Returns a mock for Storage
     *
     * @return Storage
     */
    protected function getStorageMock()
    {
        return $this->getMock(
            'eZ\\Publish\\SPI\\FieldType\\FieldStorage'
        );
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
