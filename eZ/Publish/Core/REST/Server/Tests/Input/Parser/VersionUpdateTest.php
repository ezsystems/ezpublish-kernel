<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\Repository\ContentService;
use eZ\Publish\Core\REST\Client\ContentTypeService;
use eZ\Publish\Core\REST\Client\FieldTypeService;
use eZ\Publish\Core\REST\Server\Input\Parser\VersionUpdate;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;

class VersionUpdateTest extends BaseTest
{
    /**
     * Tests the VersionUpdate parser.
     */
    public function testParse()
    {
        $inputArray = [
            'initialLanguageCode' => 'eng-US',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => [],
                    ],
                ],
            ],
            '__url' => '/content/objects/42/versions/1',
        ];

        $VersionUpdate = $this->getParser();
        $result = $VersionUpdate->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertInstanceOf(
            ContentUpdateStruct::class,
            $result,
            'VersionUpdate not created correctly.'
        );

        $this->assertEquals(
            'eng-US',
            $result->initialLanguageCode,
            'initialLanguageCode not created correctly'
        );

        foreach ($result->fields as $field) {
            $this->assertEquals(
                'foo',
                $field->value,
                'field value not created correctly'
            );
        }
    }

    /**
     * Test VersionUpdate parser throwing exception on invalid fields data.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'fields' element for VersionUpdate.
     */
    public function testParseExceptionOnInvalidFields()
    {
        $inputArray = [
            'initialLanguageCode' => 'eng-US',
            'fields' => [],
            '__url' => '/content/objects/42/versions/1',
        ];

        $VersionUpdate = $this->getParser();
        $VersionUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test VersionUpdate parser throwing exception on missing field definition identifier.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldDefinitionIdentifier' element in field data for VersionUpdate.
     */
    public function testParseExceptionOnMissingFieldDefinitionIdentifier()
    {
        $inputArray = [
            'initialLanguageCode' => 'eng-US',
            'fields' => [
                'field' => [
                    [
                        'fieldValue' => [],
                    ],
                ],
            ],
            '__url' => '/content/objects/42/versions/1',
        ];

        $VersionUpdate = $this->getParser();
        $VersionUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test VersionUpdate parser throwing exception on missing field value.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldValue' element for 'subject' identifier in VersionUpdate.
     */
    public function testParseExceptionOnMissingFieldValue()
    {
        $inputArray = [
            'initialLanguageCode' => 'eng-US',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'subject',
                    ],
                ],
            ],
            '__url' => '/content/objects/42/versions/1',
        ];

        $VersionUpdate = $this->getParser();
        $VersionUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the VersionUpdate parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\VersionUpdate
     */
    protected function internalGetParser()
    {
        return new VersionUpdate(
            $this->getContentServiceMock(),
            $this->getFieldTypeParserMock()
        );
    }

    /**
     * Get the field type parser mock object.
     *
     * @return \eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
     */
    private function getFieldTypeParserMock()
    {
        $fieldTypeParserMock = $this->getMockBuilder(FieldTypeParser::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->setConstructorArgs(
                [
                    $this->getContentServiceMock(),
                    $this->createMock(ContentTypeService::class),
                    $this->createMock(FieldTypeService::class),
                ]
            )
            ->getMock();

        $fieldTypeParserMock->expects($this->any())
            ->method('parseFieldValue')
            ->with(42, 'subject', [])
            ->will($this->returnValue('foo'));

        return $fieldTypeParserMock;
    }

    /**
     * Get the Content service mock object.
     *
     * @return \eZ\Publish\API\Repository\ContentService
     */
    protected function getContentServiceMock()
    {
        $contentServiceMock = $this->createMock(ContentService::class);

        $contentServiceMock->expects($this->any())
            ->method('newContentUpdateStruct')
            ->will(
                $this->returnValue(new ContentUpdateStruct())
            );

        return $contentServiceMock;
    }

    public function getParseHrefExpectationsMap()
    {
        return [
            ['/content/objects/42/versions/1', 'contentId', 42],
        ];
    }
}
