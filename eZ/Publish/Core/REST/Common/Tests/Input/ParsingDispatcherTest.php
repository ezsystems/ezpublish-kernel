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
class ParsingDispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testParseMissingContentType()
    {
        $message = new Common\Message();

        $dispatcher = new Common\Input\ParsingDispatcher();

        $dispatcher->parse( array(), 'text/unknown' );
    }

    public function testParse()
    {
        $message = new Common\Message();

        $parser     = $this->getMock( '\\eZ\\Publish\\Core\\REST\\Common\\Input\\Parser' );
        $dispatcher = new Common\Input\ParsingDispatcher( array(
            'text/html' => $parser,
        ) );

        $parser
            ->expects( $this->at( 0 ) )
            ->method( 'parse' )
            ->with( array( 42 ), $dispatcher )
            ->will( $this->returnValue( 23 ) );

        $this->assertSame(
            23,
            $dispatcher->parse( array( 42 ), 'text/html' )
        );
    }

    public function testParseStripFromat()
    {
        $message = new Common\Message();

        $parser     = $this->getMock( '\\eZ\\Publish\\Core\\REST\\Common\\Input\\Parser' );
        $dispatcher = new Common\Input\ParsingDispatcher( array(
            'text/html' => $parser,
        ) );

        $parser
            ->expects( $this->at( 0 ) )
            ->method( 'parse' )
            ->with( array( 42 ), $dispatcher )
            ->will( $this->returnValue( 23 ) );

        $this->assertSame(
            23,
            $dispatcher->parse( array( 42 ), 'text/html+json' )
        );
    }
}

