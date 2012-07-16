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

class SectionTest extends BaseTest
{
    /**
     * @return void
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
     * @return void
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
     * @return void
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
     * @return void
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
     * @return void
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
     * @return eZ\Publish\API\REST\Client\Input\Parser\Section;
     */
    protected function getParser()
    {
        return new Parser\Section();
    }
}
