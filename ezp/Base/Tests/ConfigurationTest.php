<?php
/**
 * File contains: Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Tests;
use ezp\Base\Configuration,
    PHPUnit_Framework_TestCase;

/**
 * Test class
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Base\Configuration $configuration
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

        $this->parserMock = $this->getMock( 'ezp\Base\Configuration\Parser' );

        $this->configuration = new Configuration(
            'test',
            array( 'base' => array( __DIR__ . '/Configuration/' ) ),
            array( 'base' => array( 'Configuration' => array( 'Parsers' => array( '.ini' => $this->parserMock ) ) ) )
        );
    }

    /**
     * Test Configuration
     *
     * @covers \ezp\Base\Configuration::parse
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
}
