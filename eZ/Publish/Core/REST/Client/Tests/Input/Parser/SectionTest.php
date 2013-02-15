<?php
/**
 * File containing a SectionTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;

class SectionTest extends BaseTest
{
    /**
     * Tests the section parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function testParse()
    {
        $sectionParser = $this->getParser();

        $inputArray = array(
            '_href'      => '/content/sections/23',
            'identifier' => 'some-section',
            'name'       => 'Some Section',
        );

        $result = $sectionParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the resulting role is in fact an instance of Section class
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $result
     *
     * @depends testParse
     */
    public function testResultIsSection( $result )
    {
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Section',
            $result
        );
    }

    /**
     * Tests if resulting section contains the ID
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $result
     *
     * @depends testParse
     */
    public function testResultContainsId( $result )
    {
        $this->assertEquals(
            '/content/sections/23',
            $result->id
        );
    }

    /**
     * Tests if resulting section contains the identifier
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $result
     *
     * @depends testParse
     */
    public function testResultContainsIdentifier( $result )
    {
        $this->assertEquals(
            'some-section',
            $result->identifier
        );
    }

    /**
     * Tests if resulting section contains the name
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $result
     *
     * @depends testParse
     */
    public function testResultContainsName( $result )
    {
        $this->assertEquals(
            'Some Section',
            $result->name
        );
    }

    /**
     * Gets the section parser
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\Section;
     */
    protected function getParser()
    {
        return new Parser\Section();
    }
}
