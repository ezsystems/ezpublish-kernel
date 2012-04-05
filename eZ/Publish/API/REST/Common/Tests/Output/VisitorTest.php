<?php
/**
 * File containing the ContentTypeServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Tests\Output;

use eZ\Publish\API\REST\Common;

/**
 * Test case for operations in the ContentTypeService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentTypeService
 * @group integration
 */
class VisitorTest extends \PHPUnit_Framework_TestCase
{
    public function testVisit()
    {
        $data = new \StdClass();

        $generator = $this->getMock( '\\eZ\\Publish\\API\\REST\\Common\\Output\\Generator' );
        $generator
            ->expects( $this->at( 0 ) )
            ->method( 'startDocument' )
            ->with( $data );

        $generator
            ->expects( $this->at( 1 ) )
            ->method( 'endDocument' )
            ->with( $data )
            ->will( $this->returnValue( 'Hello world!' ) );

        $visitor = $this->getMock(
            '\\eZ\\Publish\\API\\REST\\Common\\Output\\Visitor',
            array( 'visitValueObject' ),
            array( $generator, array() )
        );
        $visitor
            ->expects( $this->at( 0 ) )
            ->method( 'visitValueObject' )
            ->with( $data );

        $this->assertEquals(
            new Common\Message( array(), 'Hello world!' ),
            $visitor->visit( $data )
        );
    }

    public function testSetHeaders()
    {
        $data = new \StdClass();

        $generator = $this->getMock( '\\eZ\\Publish\\API\\REST\\Common\\Output\\Generator' );
        $visitor = $this->getMock(
            '\\eZ\\Publish\\API\\REST\\Common\\Output\\Visitor',
            array( 'visitValueObject' ),
            array( $generator, array() )
        );
        $visitor
            ->expects( $this->at( 0 ) )
            ->method( 'visitValueObject' )
            ->with( $data );

        $visitor->setHeader( 'Content-Type', 'text/xml' );
        $this->assertEquals(
            new Common\Message( array(
                    'Content-Type' => 'text/xml',
                ),
                null
            ),
            $visitor->visit( $data )
        );
    }
}

