<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\Repository\ObjectStateService;
use eZ\Publish\Core\REST\Server\Input\Parser\ObjectStateGroupCreate;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;

class ObjectStateGroupCreateTest extends BaseTest
{
    /**
     * Tests the ObjectStateGroupCreate parser.
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

        $objectStateGroupCreate = $this->getParser();
        $result = $objectStateGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertInstanceOf(
            ObjectStateGroupCreateStruct::class,
            $result,
            'ObjectStateGroupCreateStruct not created correctly.'
        );

        $this->assertEquals(
            'test-group',
            $result->identifier,
            'ObjectStateGroupCreateStruct identifier property not created correctly.'
        );

        $this->assertEquals(
            'eng-GB',
            $result->defaultLanguageCode,
            'ObjectStateGroupCreateStruct defaultLanguageCode property not created correctly.'
        );

        $this->assertEquals(
            ['eng-GB' => 'Test group'],
            $result->names,
            'ObjectStateGroupCreateStruct names property not created correctly.'
        );

        $this->assertEquals(
            ['eng-GB' => 'Test group description'],
            $result->descriptions,
            'ObjectStateGroupCreateStruct descriptions property not created correctly.'
        );
    }

    /**
     * Test ObjectStateGroupCreate parser throwing exception on missing identifier.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'identifier' attribute for ObjectStateGroupCreate.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = [
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

        $objectStateGroupCreate = $this->getParser();
        $objectStateGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test ObjectStateGroupCreate parser throwing exception on missing defaultLanguageCode.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'defaultLanguageCode' attribute for ObjectStateGroupCreate.
     */
    public function testParseExceptionOnMissingDefaultLanguageCode()
    {
        $inputArray = [
            'identifier' => 'test-group',
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

        $objectStateGroupCreate = $this->getParser();
        $objectStateGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test ObjectStateGroupCreate parser throwing exception on missing names.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'names' element for ObjectStateGroupCreate.
     */
    public function testParseExceptionOnMissingNames()
    {
        $inputArray = [
            'identifier' => 'test-group',
            'defaultLanguageCode' => 'eng-GB',
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description',
                    ],
                ],
            ],
        ];

        $objectStateGroupCreate = $this->getParser();
        $objectStateGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test ObjectStateGroupCreate parser throwing exception on invalid names structure.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'names' element for ObjectStateGroupCreate.
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

        $objectStateGroupCreate = $this->getParser();
        $objectStateGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the ObjectStateGroupCreate parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ObjectStateGroupCreate
     */
    protected function internalGetParser()
    {
        return new ObjectStateGroupCreate(
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
            ->method('newObjectStateGroupCreateStruct')
            ->with($this->equalTo('test-group'))
            ->will(
                $this->returnValue(new ObjectStateGroupCreateStruct(['identifier' => 'test-group']))
            );

        return $objectStateServiceMock;
    }
}
