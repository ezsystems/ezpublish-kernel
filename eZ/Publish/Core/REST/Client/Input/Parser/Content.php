<?php

/**
 * File containing the Content parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Client\Values;
use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Parser for Version.
 *
 * @todo Integrate FieldType fromHash()
 * @todo Caching for extracted embedded objects
 */
class Content extends BaseParser
{
    /**
     * VersionInfo parser.
     *
     * @var \eZ\Publish\Core\REST\Client\Input\Parser\VersionInfo
     */
    protected $versionInfoParser;

    /** @var \eZ\Publish\Core\REST\Common\Input\ParserTools */
    protected $parserTools;

    /** @var \eZ\Publish\API\Repository\ContentService */
    protected $contentService;

    /** @var \eZ\Publish\Core\REST\Common\Input\FieldTypeParser */
    protected $fieldTypeParser;

    /**
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\Core\REST\Client\Input\Parser\VersionInfo $versionInfoParser
     * @param \eZ\Publish\Core\REST\Common\Input\FieldTypeParser $fieldTypeParser
     */
    public function __construct(ParserTools $parserTools, ContentService $contentService, VersionInfo $versionInfoParser, FieldTypeParser $fieldTypeParser)
    {
        $this->parserTools = $parserTools;
        $this->contentService = $contentService;
        $this->versionInfoParser = $versionInfoParser;
        $this->fieldTypeParser = $fieldTypeParser;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $versionInfo = $this->versionInfoParser->parse(
            $data['CurrentVersion']['Version']['VersionInfo'],
            $parsingDispatcher
        );
        $fields = $this->parseFields(
            $data['CurrentVersion']['Version']['Fields'],
            str_replace('/api/ezp/v2/content/objects/', '', $versionInfo->contentInfoId)
        );

        return new Values\Content\Content(
            $this->contentService,
            array(
                'versionInfo' => $versionInfo,
                'internalFields' => $fields,
            )
        );
    }

    /**
     * Parses the fields from the given $rawFieldsData.
     *
     * @param array $rawFieldsData
     * @param string $contentId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    protected function parseFields(array $rawFieldsData, $contentId)
    {
        $fields = array();

        if (isset($rawFieldsData['field'])) {
            foreach ($rawFieldsData['field'] as $rawFieldData) {
                $fields[] = new Field(
                    array(
                        'id' => $rawFieldData['id'],
                        'fieldDefIdentifier' => $rawFieldData['fieldDefinitionIdentifier'],
                        'languageCode' => $rawFieldData['languageCode'],
                        'value' => $this->fieldTypeParser->parseFieldValue(
                            $contentId,
                            $rawFieldData['fieldDefinitionIdentifier'],
                            $rawFieldData['fieldValue']
                        ),
                    )
                );
            }
        }

        return $fields;
    }
}
