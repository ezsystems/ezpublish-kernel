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
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;

class ObjectStateGroupUpdateTest extends BaseTest
{
    /**
     * Tests the ObjectStateGroupUpdate parser
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
            $this->getObjectStateServiceMock(),
            $this->getParserTools()
        );
    }

    /**
     * Get the object state service mock object
     *
     * @return \eZ\Publish\API\Repository\ObjectStateService
     */
    protected function getObjectStateServiceMock()
    {
        $objectStateServiceMock =  $this->getMock(
            'eZ\\Publish\\Core\\Repository\\ObjectStateService',
            array(),
            array(),
            '',
            false
        );

        $objectStateServiceMock->expects( $this->any() )
            ->method( 'newObjectStateGroupUpdateStruct' )
            ->will(
                $this->returnValue( new ObjectStateGroupUpdateStruct() )
            );

        return $objectStateServiceMock;
    }
}
