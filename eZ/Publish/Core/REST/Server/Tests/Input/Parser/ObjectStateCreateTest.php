<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\ObjectStateCreate;

class ObjectStateCreateTest extends BaseTest
{
    /**
     * Tests the ObjectStateCreateTest parser
     */
    public function testParse()
    {
        $inputArray = array(
            'identifier' => 'test-state',
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description'
                    )
                )
            )
        );

        $objectStateCreate = $this->getObjectStateCreate();
        $result = $objectStateCreate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateCreateStruct',
            $result,
            'ObjectStateCreateStruct not created correctly.'
        );

        $this->assertEquals(
            'test-state',
            $result->identifier,
            'ObjectStateCreateStruct identifier property not created correctly.'
        );

        $this->assertEquals(
            0,
            $result->priority,
            'ObjectStateCreateStruct priority property not created correctly.'
        );

        $this->assertEquals(
            'eng-GB',
            $result->defaultLanguageCode,
            'ObjectStateCreateStruct defaultLanguageCode property not created correctly.'
        );

        $this->assertEquals(
            array( 'eng-GB' => 'Test state' ),
            $result->names,
            'ObjectStateCreateStruct names property not created correctly.'
        );

        $this->assertEquals(
            array( 'eng-GB' => 'Test description' ),
            $result->descriptions,
            'ObjectStateCreateStruct descriptions property not created correctly.'
        );
    }

    /**
     * Test ObjectStateCreate parser throwing exception on missing identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'identifier' attribute for ObjectStateCreate.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = array(
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description'
                    )
                )
            )
        );

        $objectStateCreate = $this->getObjectStateCreate();
        $objectStateCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ObjectStateCreate parser throwing exception on missing priority
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'priority' attribute for ObjectStateCreate.
     */
    public function testParseExceptionOnMissingPriority()
    {
        $inputArray = array(
            'identifier' => 'test-state',
            'defaultLanguageCode' => 'eng-GB',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description'
                    )
                )
            )
        );

        $objectStateCreate = $this->getObjectStateCreate();
        $objectStateCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ObjectStateCreate parser throwing exception on missing defaultLanguageCode
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'defaultLanguageCode' attribute for ObjectStateCreate.
     */
    public function testParseExceptionOnMissingDefaultLanguageCode()
    {
        $inputArray = array(
            'identifier' => 'test-state',
            'priority' => '0',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description'
                    )
                )
            )
        );

        $objectStateCreate = $this->getObjectStateCreate();
        $objectStateCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ObjectStateCreate parser throwing exception on missing names
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'names' element for ObjectStateCreate.
     */
    public function testParseExceptionOnMissingNames()
    {
        $inputArray = array(
            'identifier' => 'test-state',
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description'
                    )
                )
            )
        );

        $objectStateCreate = $this->getObjectStateCreate();
        $objectStateCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ObjectStateCreate parser throwing exception on invalid names structure
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'names' element for ObjectStateCreate.
     */
    public function testParseExceptionOnInvalidNames()
    {
        $inputArray = array(
            'identifier' => 'test-state',
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'names' => array(),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description'
                    )
                )
            )
        );

        $objectStateCreate = $this->getObjectStateCreate();
        $objectStateCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ObjectStateCreate parser throwing exception on invalid names structure with missing language code
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_languageCode' attribute for ObjectStateCreate name.
     */
    public function testParseExceptionOnInvalidNamesNoLanguageCode()
    {
        $inputArray = array(
            'identifier' => 'test-state',
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'names' => array(
                'value' => array(
                    array(
                        '#text' => 'Test state'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description'
                    )
                )
            )
        );

        $objectStateCreate = $this->getObjectStateCreate();
        $objectStateCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ObjectStateCreate parser throwing exception on invalid names structure with missing value
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing value for ObjectStateCreate name.
     */
    public function testParseExceptionOnInvalidNamesNoValue()
    {
        $inputArray = array(
            'identifier' => 'test-state',
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description'
                    )
                )
            )
        );

        $objectStateCreate = $this->getObjectStateCreate();
        $objectStateCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the ObjectStateCreate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ObjectStateCreate
     */
    protected function getObjectStateCreate()
    {
        return new ObjectStateCreate( $this->getUrlHandler(), $this->getRepository()->getObjectStateService() );
    }
}
