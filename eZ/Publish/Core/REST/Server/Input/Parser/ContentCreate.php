<?php

/**
 * File containing the ContentCreate parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\REST\Server\Values\RestContentCreateStruct;
use DateTime;

/**
 * Parser for ContentCreate.
 */
class ContentCreate extends BaseParser
{
    /**
     * Content service.
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * ContentType service.
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * FieldType parser.
     *
     * @var \eZ\Publish\Core\REST\Common\Input\FieldTypeParser
     */
    protected $fieldTypeParser;

    /**
     * LocationCreate parser.
     *
     * @var \eZ\Publish\Core\REST\Server\Input\Parser\LocationCreate
     */
    protected $locationCreateParser;

    /**
     * Parser tools.
     *
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\Core\REST\Common\Input\FieldTypeParser $fieldTypeParser
     * @param \eZ\Publish\Core\REST\Server\Input\Parser\LocationCreate $locationCreateParser
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        FieldTypeParser $fieldTypeParser,
        LocationCreate $locationCreateParser,
        ParserTools $parserTools
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->fieldTypeParser = $fieldTypeParser;
        $this->locationCreateParser = $locationCreateParser;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContentCreateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('LocationCreate', $data) || !is_array($data['LocationCreate'])) {
            throw new Exceptions\Parser("Missing or invalid 'LocationCreate' element for ContentCreate.");
        }

        $locationCreateStruct = $this->locationCreateParser->parse($data['LocationCreate'], $parsingDispatcher);

        if (!array_key_exists('ContentType', $data) || !is_array($data['ContentType'])) {
            throw new Exceptions\Parser("Missing or invalid 'ContentType' element for ContentCreate.");
        }

        if (!array_key_exists('_href', $data['ContentType'])) {
            throw new Exceptions\Parser("Missing '_href' attribute for ContentType element in ContentCreate.");
        }

        if (!array_key_exists('mainLanguageCode', $data)) {
            throw new Exceptions\Parser("Missing 'mainLanguageCode' element for ContentCreate.");
        }

        $contentType = $this->contentTypeService->loadContentType(
            $this->requestParser->parseHref($data['ContentType']['_href'], 'contentTypeId')
        );

        $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, $data['mainLanguageCode']);

        if (array_key_exists('Section', $data) && is_array($data['Section'])) {
            if (!array_key_exists('_href', $data['Section'])) {
                throw new Exceptions\Parser("Missing '_href' attribute for Section element in ContentCreate.");
            }

            $contentCreateStruct->sectionId = $this->requestParser->parseHref($data['Section']['_href'], 'sectionId');
        }

        if (array_key_exists('alwaysAvailable', $data)) {
            $contentCreateStruct->alwaysAvailable = $this->parserTools->parseBooleanValue($data['alwaysAvailable']);
        }

        if (array_key_exists('remoteId', $data)) {
            $contentCreateStruct->remoteId = $data['remoteId'];
        }

        if (array_key_exists('modificationDate', $data)) {
            $contentCreateStruct->modificationDate = new DateTime($data['modificationDate']);
        }

        if (array_key_exists('User', $data) && is_array($data['User'])) {
            if (!array_key_exists('_href', $data['User'])) {
                throw new Exceptions\Parser("Missing '_href' attribute for User element in ContentCreate.");
            }

            $contentCreateStruct->ownerId = $this->requestParser->parseHref($data['User']['_href'], 'userId');
        }

        if (!array_key_exists('fields', $data) || !is_array($data['fields']) || !is_array($data['fields']['field'])) {
            throw new Exceptions\Parser("Missing or invalid 'fields' element for ContentCreate.");
        }

        foreach ($data['fields']['field'] as $fieldData) {
            if (!array_key_exists('fieldDefinitionIdentifier', $fieldData)) {
                throw new Exceptions\Parser("Missing 'fieldDefinitionIdentifier' element in field data for ContentCreate.");
            }

            $fieldDefinition = $contentType->getFieldDefinition($fieldData['fieldDefinitionIdentifier']);
            if (!$fieldDefinition) {
                throw new Exceptions\Parser(
                    "'{$fieldData['fieldDefinitionIdentifier']}' is invalid field definition identifier for '{$contentType->identifier}' content type in ContentCreate."
                );
            }

            if (!array_key_exists('fieldValue', $fieldData)) {
                throw new Exceptions\Parser("Missing 'fieldValue' element for '{$fieldData['fieldDefinitionIdentifier']}' identifier in ContentCreate.");
            }

            $fieldValue = $this->fieldTypeParser->parseValue(
                $fieldDefinition->typeIdentifier,
                $fieldData['fieldValue']
            );

            $languageCode = null;
            if (array_key_exists('languageCode', $fieldData)) {
                $languageCode = $fieldData['languageCode'];
            }

            $contentCreateStruct->setField($fieldData['fieldDefinitionIdentifier'], $fieldValue, $languageCode);
        }

        return new RestContentCreateStruct($contentCreateStruct, $locationCreateStruct);
    }
}
