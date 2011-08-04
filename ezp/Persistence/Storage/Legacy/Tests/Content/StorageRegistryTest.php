<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\StorageRegistryTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content\StorageRegistry,
    ezp\Persistence\Fields\Storage;

/**
 * Test case for StorageRegistry
 */
class StorageRegistryTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\StorageRegistry::register
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
     * @covers ezp\Persistence\Storage\Legacy\Content\StorageRegistry::getStorage
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
     * @covers ezp\Persistence\Storage\Legacy\Content\StorageRegistry::getStorage
     * @covers ezp\Persistence\Storage\Legacy\Exception\StorageNotFoundException
     * @expectedException ezp\Persistence\Storage\Legacy\Exception\StorageNotFoundException
     * @expectedExceptionMessage Storage for type "not-found" not found.
     */
    public function testGetNotFound()
    {
        $registry = new StorageRegistry();

        $registry->getStorage( 'not-found' );
    }

    /**
     * Returns a mock for Storage
     *
     * @return Storage
     */
    protected function getStorageMock()
    {
        return $this->getMock(
            'ezp\\Persistence\\Fields\\Storage'
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
