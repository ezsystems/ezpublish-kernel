<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\ObjectStateGroupUpdate;

class ObjectStateGroupUpdateTest extends BaseTest
{
    /**
     * Tests the ObjectStateGroupUpdateTest parser
     */
    public function testParse()
    {
        $inputArray = array(
            'identifier' => 'test-group',
            'defaultLanguageCode' => 'eng-GB',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description'
                    )
                )
            )
        );

        $objectStateGroupUpdate = $this->getObjectStateGroupUpdate();
        $result = $objectStateGroupUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroupUpdateStruct',
            $result,
            'ObjectStateGroupUpdateStruct not created correctly.'
        );

        $this->assertEquals(
            'test-group',
            $result->identifier,
            'ObjectStateGroupUpdateStruct identifier property not created correctly.'
        );

        $this->assertEquals(
            'eng-GB',
            $result->defaultLanguageCode,
            'ObjectStateGroupUpdateStruct defaultLanguageCode property not created correctly.'
        );

        $this->assertEquals(
            array( 'eng-GB' => 'Test group' ),
            $result->names,
            'ObjectStateGroupUpdateStruct names property not created correctly.'
        );

        $this->assertEquals(
            array( 'eng-GB' => 'Test group description' ),
            $result->descriptions,
            'ObjectStateGroupUpdateStruct descriptions property not created correctly.'
        );
    }

    /**
     * Test ObjectStateGroupUpdate parser throwing exception on missing identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'identifier' attribute for ObjectStateGroupUpdate.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = array(
            'defaultLanguageCode' => 'eng-GB',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description'
                    )
                )
            )
        );

        $objectStateGroupUpdate = $this->getObjectStateGroupUpdate();
        $objectStateGroupUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ObjectStateGroupUpdate parser throwing exception on missing defaultLanguageCode
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'defaultLanguageCode' attribute for ObjectStateGroupUpdate.
     */
    public function testParseExceptionOnMissingDefaultLanguageCode()
    {
        $inputArray = array(
            'identifier' => 'test-group',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description'
                    )
                )
            )
        );

        $objectStateGroupUpdate = $this->getObjectStateGroupUpdate();
        $objectStateGroupUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ObjectStateGroupUpdate parser throwing exception on missing names
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'names' element for ObjectStateGroupUpdate.
     */
    public function testParseExceptionOnMissingNames()
    {
        $inputArray = array(
            'identifier' => 'test-group',
            'defaultLanguageCode' => 'eng-GB',
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description'
                    )
                )
            )
        );

        $objectStateGroupUpdate = $this->getObjectStateGroupUpdate();
        $objectStateGroupUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ObjectStateGroupUpdate parser throwing exception on invalid names structure
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'names' element for ObjectStateGroupUpdate.
     */
    public function testParseExceptionOnInvalidNames()
    {
        $inputArray = array(
            'identifier' => 'test-group',
            'defaultLanguageCode' => 'eng-GB',
            'names' => array(),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description'
                    )
                )
            )
        );

        $objectStateGroupUpdate = $this->getObjectStateGroupUpdate();
        $objectStateGroupUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the ObjectStateGroupUpdate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ObjectStateGroupUpdate
     */
    protected function getObjectStateGroupUpdate()
    {
        return new ObjectStateGroupUpdate(
            $this->getUrlHandler(),
            $this->getRepository()->getObjectStateService(),
            $this->getParserTools()
        );
    }
}
