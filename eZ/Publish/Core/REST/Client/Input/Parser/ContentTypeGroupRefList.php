<?php

/**
 * File containing the ContentTypeGroupRefList parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Client\Values;
use eZ\Publish\API\Repository\ContentTypeService;

/**
 * Parser for ContentTypeGroupRefList.
 */
class ContentTypeGroupRefList extends BaseParser
{
    /**
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @param ParserTools $parserTools
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(ParserTools $parserTools, ContentTypeService $contentTypeService)
    {
        $this->parserTools = $parserTools;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Client\Values\ContentTypeGroupRefList
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $contentTypeGroupReferences = array();
        foreach ($data['ContentTypeGroupRef'] as $groupData) {
            $contentTypeGroupReferences[] = $groupData['_href'];
        }

        return new Values\ContentTypeGroupRefList(
            $this->contentTypeService,
            $data['_href'],
            $contentTypeGroupReferences
        );
    }
}
