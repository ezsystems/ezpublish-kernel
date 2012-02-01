<?php
/**
 * File contains: Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests;
use eZ\Publish\Core\Base\Configuration,
    PHPUnit_Framework_TestCase;

/**
 * Test class
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Base\Configuration $configuration
     */
    protected $configuration;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject $parserMock
     */
    protected $parserMock;

    /**
     * Setup parserMock and configuration with parserMock to deal with .ini files
     */
    public function setUp()
    {
        parent::setUp();

        $this->parserMock = $this->getMock( 'eZ\Publish\Core\Base\Configuration\Parser' );

        $this->configuration = new Configuration(
            'test',
            array( 'base' => array( __DIR__ . '/Configuration/' ) ),
            array( 'base' => array( 'Configuration' => array( 'Parsers' => array( '.ini' => $this->parserMock ) ) ) )
        );
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
}
