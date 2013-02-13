<?php
/**
 * File containing a ObjectStateGroupTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParserTools;

class ObjectStateGroupTest extends BaseTest
{
    /**
     * Tests the ObjectStateGroup parser
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function testParse()
    {
        $objectStateGroupParser = $this->getParser();

        $inputArray = array(
            '_href'      => '/content/objectstategroups/42',
            'identifier' => 'test-group',
            'defaultLanguageCode' => 'eng-GB',
            'languageCodes' => 'eng-GB,eng-US',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group EN'
                    ),
                    array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'Test group EN US'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description EN'
                    ),
                    array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'Test group description EN US'
                    )
                )
            )
        );

        $result = $objectStateGroupParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the resulting ObjectStateGroup is in fact an instance of ObjectStateGroup class
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $result
     *
     * @depends testParse
     */
    public function testResultIsObjectStateGroup( $result )
    {
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $result
        );
    }

    /**
     * Tests that the resulting ObjectStateGroup contains the ID
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $result
     *
     * @depends testParse
     */
    public function testResultContainsId( $result )
    {
        $this->assertEquals(
            '/content/objectstategroups/42',
            $result->id
        );
    }

    /**
     * Tests that the resulting ObjectStateGroup contains identifier
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $result
     *
     * @depends testParse
     */
    public function testResultContainsIdentifier( $result )
    {
        $this->assertEquals(
            'test-group',
            $result->identifier
        );
    }

    /**
     * Tests that the resulting ObjectStateGroup contains defaultLanguageCode
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $result
     *
     * @depends testParse
     */
    public function testResultContainsDefaultLanguageCode( $result )
    {
        $this->assertEquals(
            'eng-GB',
            $result->defaultLanguageCode
        );
    }

    /**
     * Tests that the resulting ObjectStateGroup contains languageCodes
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $result
     *
     * @depends testParse
     */
    public function testResultContainsDefaultLanguageCodes( $result )
    {
        $this->assertEquals(
            array( 'eng-GB', 'eng-US' ),
            $result->languageCodes
        );
    }

    /**
     * Tests that the resulting ObjectStateGroup contains names
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $result
     *
     * @depends testParse
     */
    public function testResultContainsNames( $result )
    {
        $this->assertEquals(
            array(
                'eng-GB' => 'Test group EN',
                'eng-US' => 'Test group EN US'
            ),
            $result->getNames()
        );
    }

    /**
     * Tests that the resulting ObjectStateGroup contains descriptions
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $result
     *
     * @depends testParse
     */
    public function testResultContainsDescriptions( $result )
    {
        $this->assertEquals(
            array(
                'eng-GB' => 'Test group description EN',
                'eng-US' => 'Test group description EN US'
            ),
            $result->getDescriptions()
        );
    }

    /**
     * Gets the parser for ObjectStateGroup
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\ObjectStateGroup;
     */
    protected function getParser()
    {
        return new Parser\ObjectStateGroup(
            new ParserTools()
        );
    }
}
