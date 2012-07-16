<?php
/**
 * File containing the ContentTypeServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\Input;

use eZ\Publish\Core\REST\Common;

/**
 * Test case for operations in the ContentTypeService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentTypeService
 * @group integration
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testParseMissingContentType()
    {
        $message = new Common\Message();

        $parsingDispatcher = $this->getMock( '\\eZ\\Publish\\Core\\REST\\Common\\Input\\ParsingDispatcher' );
        $dispatcher = new Common\Input\Dispatcher( $parsingDispatcher );

        $dispatcher->parse( $message );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testParseInvalidContentType()
    {
        $message = new Common\Message(
            array(
                'Content-Type' => 'text/html',
            )
        );

        $parsingDispatcher = $this->getMock( '\\eZ\\Publish\\Core\\REST\\Common\\Input\\ParsingDispatcher' );
        $dispatcher = new Common\Input\Dispatcher( $parsingDispatcher );

        $dispatcher->parse( $message );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testParseMissingFormatHandler()
    {
        $message = new Common\Message(
            array(
                'Content-Type' => 'text/html+unknown',
            )
        );

        $parsingDispatcher = $this->getMock( '\\eZ\\Publish\\Core\\REST\\Common\\Input\\ParsingDispatcher' );
        $dispatcher = new Common\Input\Dispatcher( $parsingDispatcher );

        $dispatcher->parse( $message );
    }

    public function testParse()
    {
        $message = new Common\Message(
            array(
                'Content-Type' => 'text/html+format',
            ),
            'Hello world!'
        );

        $parsingDispatcher = $this->getMock( '\\eZ\\Publish\\Core\\REST\\Common\\Input\\ParsingDispatcher' );
        $parsingDispatcher
            ->expects( $this->at( 0 ) )
            ->method( 'parse' )
            ->with( array( 42 ), 'text/html' )
            ->will( $this->returnValue( 23 ) );

        $handler = $this->getMock( '\\eZ\\Publish\\Core\\REST\\Common\\Input\\Handler' );
        $handler
            ->expects( $this->at( 0 ) )
            ->method( 'convert' )
            ->with( 'Hello world!' )
            ->will( $this->returnValue( array( array( 42 ) ) ) );

        $dispatcher = new Common\Input\Dispatcher( $parsingDispatcher, array( 'format' => $handler ) );

        $this->assertSame(
            23,
            $dispatcher->parse( $message )
        );
    }
}

