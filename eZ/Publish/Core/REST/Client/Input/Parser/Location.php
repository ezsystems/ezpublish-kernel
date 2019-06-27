<?php

/**
 * File containing the Location parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\Core\Repository\Values;

/**
 * Parser for Location.
 */
class Location extends BaseParser
{
    /** @var \eZ\Publish\Core\REST\Common\Input\ParserTools */
    protected $parserTools;

    /**
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct(ParserTools $parserTools)
    {
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $content = $parsingDispatcher->parse($data['Content'], 'Content');

        return new Values\Content\Location(
            array(
                'contentInfo' => $content instanceof APIContent ? $content->getVersionInfo()->getContentInfo() : null,
                'id' => $data['_href'],
                'priority' => (int)$data['priority'],
                'hidden' => $data['hidden'] === 'true',
                'invisible' => $data['invisible'] === 'true',
                'remoteId' => $data['remoteId'],
                'parentLocationId' => $data['ParentLocation']['_href'],
                'pathString' => $data['pathString'],
                'depth' => (int)$data['depth'],
                'sortField' => $this->parserTools->parseDefaultSortField($data['sortField']),
                'sortOrder' => $this->parserTools->parseDefaultSortOrder($data['sortOrder']),
            )
        );
    }
}
