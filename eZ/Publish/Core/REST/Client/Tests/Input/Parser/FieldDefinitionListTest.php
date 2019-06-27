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
use eZ\Publish\Core\REST\Client\ContentTypeService;

class FieldDefinitionListTest extends BaseTest
{
    /** @var \eZ\Publish\Core\REST\Client\ContentService */
    protected $contentTypeServiceMock;

    /**
     * Tests the section parser.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testParse()
    {
        $fieldDefinitionListParser = $this->getParser();

        $inputArray = array(
            '_media-type' => 'application/vnd.ez.api.FieldDefinitionList+json',
            '_href' => '/content/types/1/fieldDefinitions',
            // Only "mock"
            'FieldDefinition' => array(
                0 => array(
                    '_media-type' => 'application/vnd.ez.api.FieldDefinition+json',
                    '_href' => '/content/types/1/fieldDefinitions/23',
                ),
                1 => array(
                    '_media-type' => 'application/vnd.ez.api.FieldDefinition+json',
                    '_href' => '/content/types/1/fieldDefinitions/42',
                ),
            ),
        );

        $this->contentTypeServiceMock->expects($this->exactly(2))
            ->method('loadFieldDefinition')
            ->with($this->isType('string'));

        $result = $fieldDefinitionListParser->parse($inputArray, $this->getParsingDispatcherMock());

        $fieldDefinitionArray = $result->getFieldDefinitions();

        $this->assertInternalType('array', $fieldDefinitionArray);
    }

    /**
     * Gets the section parser.
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\FieldDefinitionList
     */
    protected function getParser()
    {
        return new Input\Parser\FieldDefinitionList(
            new ParserTools(),
            $this->getContentTypeServiceMock()
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Client\ContentService
     */
    protected function getContentTypeServiceMock()
    {
        if (!isset($this->contentTypeServiceMock)) {
            $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);
        }

        return $this->contentTypeServiceMock;
    }
}
