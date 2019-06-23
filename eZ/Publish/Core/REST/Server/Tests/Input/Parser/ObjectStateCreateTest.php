<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\Repository\ObjectStateService;
use eZ\Publish\Core\REST\Server\Input\Parser\ObjectStateCreate;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;

class ObjectStateCreateTest extends BaseTest
{
    /**
     * Tests the ObjectStateCreate parser.
     */
    public function testParse()
    {
        $inputArray = [
            'identifier' => 'test-state',
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description',
                    ],
                ],
            ],
        ];

        $objectStateCreate = $this->getParser();
        $result = $objectStateCreate->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertInstanceOf(
            ObjectStateCreateStruct::class,
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
            ['eng-GB' => 'Test state'],
            $result->names,
            'ObjectStateCreateStruct names property not created correctly.'
        );

        $this->assertEquals(
            ['eng-GB' => 'Test description'],
            $result->descriptions,
            'ObjectStateCreateStruct descriptions property not created correctly.'
        );
    }

    /**
     * Test ObjectStateCreate parser throwing exception on missing identifier.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'identifier' attribute for ObjectStateCreate.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = [
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description',
                    ],
                ],
            ],
        ];

        $objectStateCreate = $this->getParser();
        $objectStateCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test ObjectStateCreate parser throwing exception on missing priority.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'priority' attribute for ObjectStateCreate.
     */
    public function testParseExceptionOnMissingPriority()
    {
        $inputArray = [
            'identifier' => 'test-state',
            'defaultLanguageCode' => 'eng-GB',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description',
                    ],
                ],
            ],
        ];

        $objectStateCreate = $this->getParser();
        $objectStateCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test ObjectStateCreate parser throwing exception on missing defaultLanguageCode.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'defaultLanguageCode' attribute for ObjectStateCreate.
     */
    public function testParseExceptionOnMissingDefaultLanguageCode()
    {
        $inputArray = [
            'identifier' => 'test-state',
            'priority' => '0',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description',
                    ],
                ],
            ],
        ];

        $objectStateCreate = $this->getParser();
        $objectStateCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test ObjectStateCreate parser throwing exception on missing names.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'names' element for ObjectStateCreate.
     */
    public function testParseExceptionOnMissingNames()
    {
        $inputArray = [
            'identifier' => 'test-state',
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description',
                    ],
                ],
            ],
        ];

        $objectStateCreate = $this->getParser();
        $objectStateCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test ObjectStateCreate parser throwing exception on invalid names structure.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'names' element for ObjectStateCreate.
     */
    public function testParseExceptionOnInvalidNames()
    {
        $inputArray = [
            'identifier' => 'test-state',
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'names' => [],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description',
                    ],
                ],
            ],
        ];

        $objectStateCreate = $this->getParser();
        $objectStateCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the ObjectStateCreate parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ObjectStateCreate
     */
    protected function internalGetParser()
    {
        return new ObjectStateCreate(
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
            ->method('newObjectStateCreateStruct')
            ->with($this->equalTo('test-state'))
            ->will(
                $this->returnValue(new ObjectStateCreateStruct(['identifier' => 'test-state']))
            );

        return $objectStateServiceMock;
    }
}
