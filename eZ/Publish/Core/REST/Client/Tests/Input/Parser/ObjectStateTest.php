<?php
/**
 * File containing a ObjectStateTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParserTools;

class ObjectStateTest extends BaseTest
{
    /**
     * Tests the ObjectState parser
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function testParse()
    {
        $objectStateParser = $this->getParser();

        $inputArray = array(
            '_href'      => '/content/objectstategroups/42/objectstates/21',
            'identifier' => 'test-state',
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'languageCodes' => 'eng-GB,eng-US',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state EN'
                    ),
                    array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'Test state EN US'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state description EN'
                    ),
                    array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'Test state description EN US'
                    )
                )
            )
        );

        $result = $objectStateParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the resulting ObjectState is in fact an instance of ObjectState class
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $result
     *
     * @depends testParse
     */
    public function testResultIsObjectState( $result )
    {
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $result
        );
    }

    /**
     * Tests that the resulting ObjectState contains the ID
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $result
     *
     * @depends testParse
     */
    public function testResultContainsId( $result )
    {
        $this->assertEquals(
            '/content/objectstategroups/42/objectstates/21',
            $result->id
        );
    }

    /**
     * Tests that the resulting ObjectState contains identifier
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $result
     *
     * @depends testParse
     */
    public function testResultContainsIdentifier( $result )
    {
        $this->assertEquals(
            'test-state',
            $result->identifier
        );
    }

    /**
     * Tests that the resulting ObjectState contains priority
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $result
     *
     * @depends testParse
     */
    public function testResultContainsPriority( $result )
    {
        $this->assertEquals(
            0,
            $result->priority
        );
    }

    /**
     * Tests that the resulting ObjectState contains defaultLanguageCode
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $result
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
     * Tests that the resulting ObjectState contains languageCodes
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $result
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
     * Tests that the resulting ObjectState contains names
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $result
     *
     * @depends testParse
     */
    public function testResultContainsNames( $result )
    {
        $this->assertEquals(
            array(
                'eng-GB' => 'Test state EN',
                'eng-US' => 'Test state EN US'
            ),
            $result->getNames()
        );
    }

    /**
     * Tests that the resulting ObjectState contains descriptions
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $result
     *
     * @depends testParse
     */
    public function testResultContainsDescriptions( $result )
    {
        $this->assertEquals(
            array(
                'eng-GB' => 'Test state description EN',
                'eng-US' => 'Test state description EN US'
            ),
            $result->getDescriptions()
        );
    }

    /**
     * Gets the parser for ObjectState
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\ObjectState;
     */
    protected function getParser()
    {
        return new Parser\ObjectState(
            new ParserTools()
        );
    }
}
