<?php

/**
 * File containing the ContentTypeGroupInput parser class.
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
 * Parser for ContentTypeGroupInput.
 */
class ContentTypeGroupInput extends BaseParser
{
    /**
     * ContentType service.
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /** @var \eZ\Publish\Core\REST\Common\Input\ParserTools */
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
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        // Since ContentTypeGroupInput is used both for creating and updating ContentTypeGroup and identifier is not
        // required when updating ContentTypeGroup, we need to rely on PAPI to throw the exception on missing
        // identifier when creating a ContentTypeGroup
        // @todo Bring in line with XSD which says that identifier is required always

        $contentTypeGroupIdentifier = null;
        if (array_key_exists('identifier', $data)) {
            $contentTypeGroupIdentifier = $data['identifier'];
        }

        $contentTypeGroupCreateStruct = $this->contentTypeService->newContentTypeGroupCreateStruct($contentTypeGroupIdentifier);

        if (array_key_exists('modificationDate', $data)) {
            $contentTypeGroupCreateStruct->creationDate = new DateTime($data['modificationDate']);
        }

        // @todo mainLanguageCode, names, descriptions?

        if (array_key_exists('User', $data) && is_array($data['User'])) {
            if (!array_key_exists('_href', $data['User'])) {
                throw new Exceptions\Parser("Missing '_href' attribute for User element in ContentTypeGroupInput.");
            }

            $contentTypeGroupCreateStruct->creatorId = $this->requestParser->parseHref($data['User']['_href'], 'userId');
        }

        return $contentTypeGroupCreateStruct;
    }
}
