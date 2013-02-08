<?php
/**
 * File contains: Test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests;

use eZ\Publish\Core\Base\Configuration;
use PHPUnit_Framework_TestCase;

/**
 * Test class
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Base\Configuration
     */
    protected $configuration;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $parserMock;

    /**
     * Setup parserMock and configuration with parserMock to deal with .ini files
     */
    public function setUp()
    {
        parent::setUp();

        $this->parserMock = $this->getMock( 'eZ\\Publish\\Core\\Base\\Configuration\\Parser' );

        $this->configuration = new Configuration(
            'test',
            array( 'base' => array( __DIR__ . '/Configuration/' ) ),
            array( 'base' => array( 'Configuration' => array( 'Parsers' => array( '.ini' => $this->parserMock ) ) ) )
        );
    }

    /**
     * Tear down test (properties)
     */
    protected function tearDown()
    {
        unset( $this->parserMock );
        unset( $this->configuration );
        parent::tearDown();
    }

    /**
     * Test Configuration
     *
     * @covers \eZ\Publish\Core\Base\Configuration::load
     * @covers \eZ\Publish\Core\Base\Configuration::parse
     */
    public function testParserExecution()
    {
        $this->parserMock->expects( $this->once() )
            ->method( 'parse' )
            ->with(
                $this->equalTo( __DIR__ . '/Configuration/test.ini' ),
                $this->equalTo( file_get_contents( __DIR__ . '/Configuration/test.ini' ) )
            )->will( $this->returnValue( array() ) );
        $this->configuration->load();
    }

    /**
     * Test Configuration
     *
     * @covers \eZ\Publish\Core\Base\Configuration::load
     * @covers \eZ\Publish\Core\Base\Configuration::parse
     */
    public function testParsing()
    {
        $config = array(
            'Test' => array(
                'String' => 'test ',
                'Int' => 42,
                'Bool' => true,
                'Array' => array( 1 ),
            )
        );
        $this->parserMock->expects( $this->once() )
            ->method( 'parse' )
            ->will( $this->returnValue( $config ) );
        $this->configuration->load();

        self::assertSame( $config, $this->configuration->getAll() );
    }

    /**
     * Test Configuration
     *
     * @covers \eZ\Publish\Core\Base\Configuration::load
     * @covers \eZ\Publish\Core\Base\Configuration::parse
     * @covers \eZ\Publish\Core\Base\Configuration::recursiveArrayClearing
     */
    public function testParsingAndArrayClearing()
    {
        $config = array(
            'Test' => array(
                'String' => 'test ',
                'Int' => 42,
                'Bool' => true,
                'Array' => array( Configuration::TEMP_INI_UNSET_VAR ),
            )
        );
        $this->parserMock->expects( $this->once() )
            ->method( 'parse' )
            ->will( $this->returnValue( $config ) );
        $this->configuration->load();

        $config['Test']['Array'] = array();// Change array to excepted return value
        self::assertSame( $config, $this->configuration->getAll() );
    }

    /**
     * Test Configuration
     *
     * @covers \eZ\Publish\Core\Base\Configuration::load
     * @covers \eZ\Publish\Core\Base\Configuration::parse
     * @covers \eZ\Publish\Core\Base\Configuration::recursiveArrayClearing
     */
    public function testParsingAndRecursiveArrayClearing()
    {
        $config = array(
            'Test' => array(
                'String' => 'test ',
                'Int' => 42,
                'Bool' => true,
                'Array' => array(
                    'one' => array( 1 ),
                    'clear' => array( Configuration::TEMP_INI_UNSET_VAR ),
                ),
            )
        );
        $this->parserMock->expects( $this->once() )
            ->method( 'parse' )
            ->will( $this->returnValue( $config ) );
        $this->configuration->load();

        $config['Test']['Array']['clear'] = array();// Change array to excepted return value
        self::assertSame( $config, $this->configuration->getAll() );
    }

    /**
     * Test Configuration
     *
     * @covers \eZ\Publish\Core\Base\Configuration::load
     * @covers \eZ\Publish\Core\Base\Configuration::parse
     */
    public function testParsingExtendedSections()
    {
        $config = array(
            'Test' => array(
                'String' => 'test ',
                'Int' => 42,
                'Bool' => true,
                'Array' => array( 1 ),
            ),
            'Test3:Test2:Test' => array(),
            'Test2:Test' => array( 'Float' => 3.4 ),
        );
        $this->parserMock->expects( $this->once() )
            ->method( 'parse' )
            ->will( $this->returnValue( $config ) );
        $this->configuration->load();

        // Set extended value as we expect it
        $config['Test2:Test'] += $config['Test'];
        $config['Test3:Test2:Test'] = $config['Test2:Test'];

        self::assertEquals( $config, $this->configuration->getAll() );
    }

    /**
     * Test Configuration
     *
     * @covers \eZ\Publish\Core\Base\Configuration::load
     */
    public function testLoadGlobalConfig()
    {
        $globalConfig = array(
            'NonExistingSection' => array( 'Setting1' => 'Value' ),
            'ExistingSection' => array(
                'Setting1' => 'Value',
                'Setting2' => array( 'key1' => 1, 'Key2' => 2 )
            )
        );
        $configuration = new Configuration(
            'test',
            array( 'base' => array( __DIR__ . '/Configuration/' ) ),
            array(
                'base' => array( 'Configuration' => array( 'Parsers' => array( '.ini' => $this->parserMock ) ) ),
                'test' => $globalConfig
            )
        );
        $config = array(
            'ExistingSection' => array(
                'Setting1' => 'ValueValue',
                'Setting2' => array( 'key1' => 3, 'Key2' => 3, 'Key3' => 3 ),
                'Setting3' => 'ValueValue'
            )
        );
        $this->parserMock->expects( $this->once() )
            ->method( 'parse' )
            ->will( $this->returnValue( $config ) );
        $configuration->load();

        // Set extended value as we expect it
        $globalConfig['ExistingSection']['Setting2']['Key3'] = 3;
        $globalConfig['ExistingSection']['Setting3'] = 'ValueValue';

        self::assertEquals( $globalConfig, $configuration->getAll() );
    }
}
