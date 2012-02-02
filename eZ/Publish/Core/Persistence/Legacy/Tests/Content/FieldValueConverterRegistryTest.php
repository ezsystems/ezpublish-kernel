<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValueConverterRegistryTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry;

/**
 * Test case for FieldValue Converter Registry
 */
class FieldValueConverterRegistryTest extends TestCase
{
    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry::register
     */
    public function testRegister()
    {
        $registry = new Registry();

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
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry::getConverter
     */
    public function testGetStorage()
    {
        $registry = new Registry();

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
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry::getConverter
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     * @expectedException eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     * @expectedExceptionMessage FieldValue Converter for type "not-found" not found.
     */
    public function testGetNotFound()
    {
        $registry = new Registry();

        $registry->getConverter( 'not-found' );
    }

    /**
     * Returns a mock for Storage
     *
     * @return Storage
     */
    protected function getFieldValueConverterMock()
    {
        return $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter'
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
