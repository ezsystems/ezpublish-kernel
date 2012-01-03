<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\ConfiguratorTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Configurator,
    ezp\Persistence\Storage\Legacy\Content;

/**
 * Test case for Configurator
 */
class ConfiguratorTest extends TestCase
{
    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Configurator::__construct
     */
    public function testCtor()
    {
        $config = array( 'some' => 'value' );

        $configurator = new Configurator( $config );

        $this->assertAttributeEquals(
            $config,
            'config',
            $configurator
        );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Configurator::getDsn
     */
    public function testGetDsn()
    {
        $dsn = 'sqlite://:memory:';
        $config = array( 'dsn' => $dsn );

        $configurator = new Configurator( $config );

        $this->assertEquals(
            $dsn,
            $configurator->getDsn()
        );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Configurator::getDsn
     * @expectedException \RuntimeException
     */
    public function testGetDsnFailure()
    {
        $config = array();

        $configurator = new Configurator( $config );

        $configurator->getDsn();
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Configurator::shouldDeferTypeUpdates
     */
    public function testShouldDeferTypeUpdates()
    {
        $config = array( 'defer_type_update' => true );

        $configurator = new Configurator( $config );

        $this->assertTrue(
            $configurator->shouldDeferTypeUpdates()
        );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Configurator::shouldDeferTypeUpdates
     */
    public function testShouldDeferTypeUpdatesFalse()
    {
        $config = array( 'defer_type_update' => false );

        $configurator = new Configurator( $config );

        $this->assertFalse(
            $configurator->shouldDeferTypeUpdates()
        );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Configurator::shouldDeferTypeUpdates
     */
    public function testShouldDeferTypeUpdatesNotSet()
    {
        $config = array();

        $configurator = new Configurator( $config );

        $this->assertFalse(
            $configurator->shouldDeferTypeUpdates()
        );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Configurator::configureExternalStorages
     */
    public function testConfigureExternalStorages()
    {
        $storageRegistry = new Content\StorageRegistry();
        $storageMock = $this->getMock(
            'ezp\\Persistence\\Fields\\Storage',
            array(),
            array(),
            'SomeFanvyStorageMockClass',
            false
        );

        $config = array(
            'external_storages' => array(
                'foo' => 'SomeFanvyStorageMockClass',
                'bar' => 'SomeFanvyStorageMockClass',
            ),
        );

        $configurator = new Configurator( $config );
        $configurator->configureExternalStorages( $storageRegistry );

        $this->assertInstanceOf(
            'SomeFanvyStorageMockClass',
            $storageRegistry->getStorage( 'foo' )
        );
        $this->assertInstanceOf(
            'SomeFanvyStorageMockClass',
            $storageRegistry->getStorage( 'bar' )
        );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Configurator::configureFieldConverter
     */
    public function testConfigureFieldConverters()
    {
        $converterRegistry = new Content\FieldValue\Converter\Registry();
        $storageMock = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter',
            array(),
            array(),
            'SomeFanvyFieldConverterMockClass',
            false
        );

        $config = array(
            'field_converter' => array(
                'foo' => 'SomeFanvyFieldConverterMockClass',
                'bar' => 'SomeFanvyFieldConverterMockClass',
            ),
        );

        $configurator = new Configurator( $config );
        $configurator->configureFieldConverter( $converterRegistry );

        $this->assertInstanceOf(
            'SomeFanvyFieldConverterMockClass',
            $converterRegistry->getConverter( 'foo' )
        );
        $this->assertInstanceOf(
            'SomeFanvyFieldConverterMockClass',
            $converterRegistry->getConverter( 'bar' )
        );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Configurator::configureTransformationRules
     */
    public function testConfigureTransformationRules()
    {
        $processorMock = $this->getMock(
            '\ezp\Persistence\Storage\Legacy\Content\Search\TransformationProcessor',
            array(),
            array(),
            '',
            false
        );

        $processorMock->expects( $this->at( 0 ) )
            ->method( 'loadRules' )
            ->with( 'some_rule_file.tr' );
        $processorMock->expects( $this->at( 1 ) )
            ->method( 'loadRules' )
            ->with( 'another_rule_file.tr' );

        $config = array(
            'transformation_rule_files' => array(
                'some_rule_file.tr',
                'another_rule_file.tr',
            )
        );

        $configurator = new Configurator( $config );
        $configurator->configureTransformationRules( $processorMock );
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
