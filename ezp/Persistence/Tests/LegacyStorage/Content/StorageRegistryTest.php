<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\StorageRegistryTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegacyStorage\Content;
use ezp\Persistence\Tests\LegacyStorage\TestCase,
    ezp\Persistence\LegacyStorage\Content\StorageRegistry,
    ezp\Persistence\Fields\StorageInterface;

/**
 * Test case for StorageRegistry
 */
class StorageRegistryTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\StorageRegistry::register
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
     * @covers ezp\Persistence\LegacyStorage\Content\StorageRegistry::getStorage
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
     * Returns a mock for StorageInterface
     *
     * @return StorageInterface
     */
    protected function getStorageMock()
    {
        return $this->getMock(
            'ezp\Persistence\Fields\StorageInterface'
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
