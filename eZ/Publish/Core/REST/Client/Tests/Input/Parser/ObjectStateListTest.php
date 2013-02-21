<?php
/**
 * File containing a ObjectStateListTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;

class ObjectStateListTest extends BaseTest
{
    /**
     * Tests the parsing of ObjectStateList
     */
    public function testParse()
    {
        $stateListParser = $this->getParser();

        $inputArray = array(
            'ObjectState'  => array(
                array( '_media-type' => 'application/vnd.ez.api.ObjectState+xml' ),
                array( '_media-type' => 'application/vnd.ez.api.ObjectState+xml' ),
            ),
        );

        $this->getParsingDispatcherMock()
            ->expects( $this->exactly( 2 ) )
            ->method( 'parse' )
            ->with(
                array( '_media-type' => 'application/vnd.ez.api.ObjectState+xml' ),
                'application/vnd.ez.api.ObjectState+xml'
            )
            ->will( $this->returnValue( 'foo' ) );

        $result = $stateListParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertEquals(
            array( 'foo', 'foo' ),
            $result
        );
    }

    /**
     * Gets the ObjectStateList parser
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\ObjectStateList;
     */
    protected function getParser()
    {
        return new Parser\ObjectStateList();
    }
}
