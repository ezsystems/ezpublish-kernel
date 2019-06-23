<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\Repository\ObjectStateService;
use eZ\Publish\Core\REST\Server\Input\Parser\ObjectStateGroupUpdate;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;

class ObjectStateGroupUpdateTest extends BaseTest
{
    /**
     * Tests the ObjectStateGroupUpdate parser.
     */
    public function testParse()
    {
        $inputArray = [
            'identifier' => 'test-group',
            'defaultLanguageCode' => 'eng-GB',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description',
                    ],
                ],
            ],
        ];

        $objectStateGroupUpdate = $this->getParser();
        $result = $objectStateGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertInstanceOf(
            ObjectStateGroupUpdateStruct::class,
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
            ['eng-GB' => 'Test group'],
            $result->names,
            'ObjectStateGroupUpdateStruct names property not created correctly.'
        );

        $this->assertEquals(
            ['eng-GB' => 'Test group description'],
            $result->descriptions,
            'ObjectStateGroupUpdateStruct descriptions property not created correctly.'
        );
    }

    /**
     * Test ObjectStateGroupUpdate parser throwing exception on invalid names structure.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'names' element for ObjectStateGroupUpdate.
     */
    public function testParseExceptionOnInvalidNames()
    {
        $inputArray = [
            'identifier' => 'test-group',
            'defaultLanguageCode' => 'eng-GB',
            'names' => [],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description',
                    ],
                ],
            ],
        ];

        $objectStateGroupUpdate = $this->getParser();
        $objectStateGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the ObjectStateGroupUpdate parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ObjectStateGroupUpdate
     */
    protected function internalGetParser()
    {
        return new ObjectStateGroupUpdate(
            $this->getObjectStateServiceMock(),
            $this->getParserTools()
        );
    }

    /**
     * Get the object state service mock object.
     *
     * @return \eZ\Publish\API\Repository\ObjectStateService
     */
    protected function getObjectStateServiceMock()
    {
        $objectStateServiceMock = $this->createMock(ObjectStateService::class);

        $objectStateServiceMock->expects($this->any())
            ->method('newObjectStateGroupUpdateStruct')
            ->will(
                $this->returnValue(new ObjectStateGroupUpdateStruct())
            );

        return $objectStateServiceMock;
    }
}
