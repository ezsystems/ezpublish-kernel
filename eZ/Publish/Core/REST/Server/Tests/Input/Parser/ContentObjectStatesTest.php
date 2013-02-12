<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser;

class ContentObjectStatesTest extends BaseTest
{
    /**
     * Tests the ContentObjectStates parser
     */
    public function testParse()
    {
        $inputArray = array(
            'ObjectState' => array(
                array(
                    '_href' => '/content/objectstategroups/42/objectstates/21'
                )
            )
        );

        $objectState = $this->getParser();
        $result = $objectState->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInternalType(
            'array',
            $result,
            'ContentObjectStates not parsed correctly'
        );

        $this->assertNotEmpty(
            $result,
            'ContentObjectStates has no ObjectState elements'
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\Core\\REST\\Common\\Values\\RestObjectState',
            $result[0],
            'ObjectState not created correctly.'
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $result[0]->objectState,
            'Inner ObjectState not created correctly.'
        );

        $this->assertEquals(
            21,
            $result[0]->objectState->id,
            'Inner ObjectState id property not created correctly.'
        );

        $this->assertEquals(
            42,
            $result[0]->groupId,
            'groupId property not created correctly.'
        );
    }

    /**
     * Test ContentObjectStates parser throwing exception on missing href
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for ObjectState.
     */
    public function testParseExceptionOnMissingHref()
    {
        $inputArray = array(
            'ObjectState' => array(
                array(
                    '_href' => '/content/objectstategroups/42/objectstates/21'
                ),
                array()
            )
        );

        $objectState = $this->getParser();
        $objectState->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Gets the ContentObjectStates parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ContentObjectStates;
     */
    protected function getParser()
    {
        return new Parser\ContentObjectStates( $this->getUrlHandler() );
    }
}
