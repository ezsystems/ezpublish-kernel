<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\Parser;

class ContentObjectStatesTest extends BaseTest
{
    /**
     * Tests the ContentObjectStates parser.
     */
    public function testParse()
    {
        $inputArray = [
            'ObjectState' => [
                [
                    '_href' => '/content/objectstategroups/42/objectstates/21',
                ],
            ],
        ];

        $objectState = $this->getParser();
        $result = $objectState->parse($inputArray, $this->getParsingDispatcherMock());

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
     * Test ContentObjectStates parser throwing exception on missing href.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for ObjectState.
     */
    public function testParseExceptionOnMissingHref()
    {
        $inputArray = [
            'ObjectState' => [
                [
                    '_href' => '/content/objectstategroups/42/objectstates/21',
                ],
                [],
            ],
        ];

        $objectState = $this->getParser();
        $objectState->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function getParseHrefExpectationsMap()
    {
        return [
            ['/content/objectstategroups/42/objectstates/21', 'objectStateId', 21],
            ['/content/objectstategroups/42/objectstates/21', 'objectStateGroupId', 42],
        ];
    }

    /**
     * Gets the ContentObjectStates parser.
     *
     * @return \eZ\Publish\Core\REST\Common\Input\Parser\ContentObjectStates;
     */
    protected function internalGetParser()
    {
        return new Parser\ContentObjectStates();
    }
}
