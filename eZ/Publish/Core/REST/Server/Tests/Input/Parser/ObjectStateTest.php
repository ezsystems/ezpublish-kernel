<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\ObjectState;

class ObjectStateTest extends BaseTest
{
    /**
     * Tests the ObjectStat parser
     */
    public function testParse()
    {
        $inputArray = array(
            '_href' => '/content/objectstategroups/42/objectstates/21',
        );

        $objectState = $this->getObjectState();
        $result = $objectState->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\Core\\REST\\Common\\Values\\RestObjectState',
            $result,
            'ObjectState not created correctly.'
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $result->objectState,
            'Inner ObjectState not created correctly.'
        );

        $this->assertEquals(
            21,
            $result->objectState->id,
            'Inner ObjectState id property not created correctly.'
        );

        $this->assertEquals(
            42,
            $result->groupId,
            'groupId property not created correctly.'
        );
    }

    /**
     * Test ObjectState parser throwing exception on missing href
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for ObjectState.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = array();

        $objectState = $this->getObjectState();
        $objectState->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the ObjectState parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ObjectState
     */
    protected function getObjectState()
    {
        return new ObjectState( $this->getUrlHandler() );
    }
}
