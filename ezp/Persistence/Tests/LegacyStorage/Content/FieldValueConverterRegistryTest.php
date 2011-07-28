<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\FieldValueConverterRegistryTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegacyStorage\Content;
use ezp\Persistence\Tests\LegacyStorage\TestCase,
    ezp\Persistence\LegacyStorage\Content\FieldValueConverterRegistry,
    ezp\Persistence\LegacyStorage\Content\FieldValueConverter;

/**
 * Test case for FieldValueConverterRegistry
 */
class FieldValueConverterRegistryTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\FieldValueConverterRegistry::register
     */
    public function testRegister()
    {
        $registry = new FieldValueConverterRegistry();

        $converter = $this->getFieldValueConverterMock();

        $registry->register( 'some-type', $converter );

        $this->assertAttributeSame(
            array(
                'some-type' => $converter,
            ),
            'converterMap',
            $registry
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\FieldValueConverterRegistry::getConverter
     */
    public function testGetStorage()
    {
        $registry = new FieldValueConverterRegistry();

        $converter = $this->getFieldValueConverterMock();
        $registry->register( 'some-type', $converter );

        $res = $registry->getConverter( 'some-type' );

        $this->assertSame(
            $converter,
            $res
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\FieldValueConverterRegistry::getConverter
     * @expectedException ezp\Persistence\LegacyStorage\Exception\FieldValueConverterNotFoundException
     * @expectedExceptionMessage FieldValueConverter for type "not-found" not found.
     */
    public function testGetNotFound()
    {
        $registry = new FieldValueConverterRegistry();

        $registry->getConverter( 'not-found' );
    }

    /**
     * Returns a mock for StorageInterface
     *
     * @return StorageInterface
     */
    protected function getFieldValueConverterMock()
    {
        return $this->getMockForAbstractClass(
            'ezp\\Persistence\\LegacyStorage\\Content\\FieldValueConverter'
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
