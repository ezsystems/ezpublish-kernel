<?php

/**
 * File containing the ContentTypeUpdate parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\ContentTypeService;
use DateTime;

/**
 * Parser for ContentTypeUpdate.
 */
class ContentTypeUpdate extends BaseParser
{
    /**
     * ContentType service.
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Parser tools.
     *
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct(ContentTypeService $contentTypeService, ParserTools $parserTools)
    {
        $this->contentTypeService = $contentTypeService;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $contentTypeUpdateStruct = $this->contentTypeService->newContentTypeUpdateStruct();

        if (array_key_exists('identifier', $data)) {
            $contentTypeUpdateStruct->identifier = $data['identifier'];
        }

        if (array_key_exists('mainLanguageCode', $data)) {
            $contentTypeUpdateStruct->mainLanguageCode = $data['mainLanguageCode'];
        }

        if (array_key_exists('remoteId', $data)) {
            $contentTypeUpdateStruct->remoteId = $data['remoteId'];
        }

        if (array_key_exists('urlAliasSchema', $data)) {
            $contentTypeUpdateStruct->urlAliasSchema = $data['urlAliasSchema'];
        }

        if (array_key_exists('nameSchema', $data)) {
            $contentTypeUpdateStruct->nameSchema = $data['nameSchema'];
        }

        if (array_key_exists('isContainer', $data)) {
            $contentTypeUpdateStruct->isContainer = $this->parserTools->parseBooleanValue($data['isContainer']);
        }

        if (array_key_exists('defaultSortField', $data)) {
            $contentTypeUpdateStruct->defaultSortField = $this->parserTools->parseDefaultSortField($data['defaultSortField']);
        }

        if (array_key_exists('defaultSortOrder', $data)) {
            $contentTypeUpdateStruct->defaultSortOrder = $this->parserTools->parseDefaultSortOrder($data['defaultSortOrder']);
        }

        if (array_key_exists('defaultAlwaysAvailable', $data)) {
            $contentTypeUpdateStruct->defaultAlwaysAvailable = $this->parserTools->parseBooleanValue($data['defaultAlwaysAvailable']);
        }

        if (array_key_exists('names', $data)) {
            if (!is_array($data['names']) || !array_key_exists('value', $data['names']) || !is_array($data['names']['value'])) {
                throw new Exceptions\Parser("Invalid 'names' element for ContentTypeUpdate.");
            }

            $contentTypeUpdateStruct->names = $this->parserTools->parseTranslatableList($data['names']);
        }

        if (array_key_exists('descriptions', $data)) {
            if (!is_array($data['descriptions']) || !array_key_exists('value', $data['descriptions']) || !is_array($data['descriptions']['value'])) {
                throw new Exceptions\Parser("Invalid 'descriptions' element for ContentTypeUpdate.");
            }

            $contentTypeUpdateStruct->descriptions = $this->parserTools->parseTranslatableList($data['descriptions']);
        }

        if (array_key_exists('modificationDate', $data)) {
            $contentTypeUpdateStruct->modificationDate = new DateTime($data['modificationDate']);
        }

        if (array_key_exists('User', $data)) {
            if (!array_key_exists('_href', $data['User'])) {
                throw new Exceptions\Parser("Missing '_href' attribute for User element in ContentTypeUpdate.");
            }

            $contentTypeUpdateStruct->modifierId = $this->requestParser->parseHref($data['User']['_href'], 'userId');
        }

        return $contentTypeUpdateStruct;
    }
}
