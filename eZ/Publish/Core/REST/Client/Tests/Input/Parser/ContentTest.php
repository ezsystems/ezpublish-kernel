<?php

/**
 * File containing a ContentTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Client\Input\Parser\VersionInfo;
use eZ\Publish\Core\REST\Client\ContentService;
use eZ\Publish\API\Repository\Values;

class ContentTest extends BaseTest
{
    /** @var \eZ\Publish\Core\REST\Client\Input\Parser\VersionInfo */
    protected $versionInfoParserMock;

    /** @var \eZ\Publish\Core\REST\Client\ContentService */
    protected $contentServiceMock;

    /** @var \eZ\Publish\Core\REST\Common\Input\FieldTypeParser */
    protected $fieldTypeParserMock;

    /**
     * Tests the section parser.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testParse()
    {
        $relationParser = $this->getParser();

        $inputArray = array(
            '_media-type' => 'application/vnd.ez.api.Version+json',
            '_href' => '/api/ezp/v2/content/objects/10/versions/2',
            'CurrentVersion' => array(
                'Version' => array(
                    'VersionInfo' => array(),
                    'Fields' => array(
                        'field' => array(
                            0 => array(
                                'id' => 19,
                                'fieldDefinitionIdentifier' => 'first_name',
                                'languageCode' => 'eng-US',
                                'fieldValue' => 'Anonymous',
                            ),
                            1 => array(
                                'id' => 20,
                                'fieldDefinitionIdentifier' => 'last_name',
                                'languageCode' => 'eng-US',
                                'fieldValue' => 'User',
                            ),
                        ),
                    ),
                ),
            ),
        );

        $versionInfoMock = new \stdClass();
        $versionInfoMock->contentInfoId = '/api/ezp/v2/content/objects/10';

        $this->getVersionInfoParserMock()->expects($this->once())
            ->method('parse')
            ->with(
                $this->equalTo(array()),
                $this->isInstanceOf(ParsingDispatcher::class)
            )->will($this->returnValue($versionInfoMock));

        $this->getFieldTypeParserMock()->expects($this->exactly(2))
            ->method('parseFieldValue')
            ->will($this->returnValue('MockedValue'));

        $result = $relationParser->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * @depends testParse
     */
    public function testParsedVersionInfo($parsedContent)
    {
        $this->assertInstanceOf(
            // Mocked result
            'stdClass',
            $parsedContent->versionInfo
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedFirstNameField($parsedContent)
    {
        $this->assertEquals(
            // Mocked result
            new Values\Content\Field(
                array(
                    'id' => 19,
                    'fieldDefIdentifier' => 'first_name',
                    'languageCode' => 'eng-US',
                    'value' => 'MockedValue',
                )
            ),
            $parsedContent->getField('first_name', 'eng-US')
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedLastNameField($parsedContent)
    {
        $this->assertEquals(
            // Mocked result
            new Values\Content\Field(
                array(
                    'id' => 20,
                    'fieldDefIdentifier' => 'last_name',
                    'languageCode' => 'eng-US',
                    'value' => 'MockedValue',
                )
            ),
            $parsedContent->getField('last_name', 'eng-US')
        );
    }

    /**
     * Gets the section parser.
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\Content
     */
    protected function getParser()
    {
        return new Input\Parser\Content(
            new ParserTools(),
            $this->getContentServiceMock(),
            $this->getVersionInfoParserMock(),
            $this->getFieldTypeParserMock()
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\VersionInfo
     */
    protected function getVersionInfoParserMock()
    {
        if (!isset($this->versionInfoParserMock)) {
            $this->versionInfoParserMock = $this->createMock(VersionInfo::class);
        }

        return $this->versionInfoParserMock;
    }

    /**
     * @return \eZ\Publish\Core\REST\Client\ContentService
     */
    protected function getContentServiceMock()
    {
        if (!isset($this->contentServiceMock)) {
            $this->contentServiceMock = $this->createMock(ContentService::class);
        }

        return $this->contentServiceMock;
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\Input\FieldTypeParser
     */
    protected function getFieldTypeParserMock()
    {
        if (!isset($this->fieldTypeParserMock)) {
            $this->fieldTypeParserMock = $this->createMock(FieldTypeParser::class);
        }

        return $this->fieldTypeParserMock;
    }
}
