<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client\Tests\Input\Parser;

use eZ\Publish\API\REST\Client\Input\Parser;

class SectionListTest extends BaseTest
{
    /**
     * @return void
     */
    public function testParse()
    {
        $sectionParser = $this->getParser();

        $inputArray = array(
            'section'  => array(
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
     * @return eZ\Publish\API\REST\Client\Input\Parser\SectionList;
     */
    protected function getParser()
    {
        return new Parser\SectionList();
    }
}
