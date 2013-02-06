<?php
/**
 * File containing a SectionListTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;

class SectionListTest extends BaseTest
{
    /**
     * Tests the parsing of role list
     */
    public function testParse()
    {
        $sectionParser = $this->getParser();

        $inputArray = array(
            'Section'  => array(
                array( '_media-type' => 'application/vnd.ez.api.Section+xml' ),
                array( '_media-type' => 'application/vnd.ez.api.Section+xml' ),
            ),
        );

        $this->getParsingDispatcherMock()
            ->expects( $this->exactly( 2 ) )
            ->method( 'parse' )
            ->with(
                array( '_media-type' => 'application/vnd.ez.api.Section+xml' ),
                'application/vnd.ez.api.Section+xml'
            )
            ->will( $this->returnValue( 'foo' ) );

        $result = $sectionParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertEquals(
            array( 'foo', 'foo' ),
            $result
        );
    }

    /**
     * Gets the section list parser
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\SectionList;
     */
    protected function getParser()
    {
        return new Parser\SectionList();
    }
}
