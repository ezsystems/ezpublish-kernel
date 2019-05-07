<?php

/**
 * File containing the ViewInput parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client\Input\Parser;

use EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher;
use EzSystems\EzPlatformRestCommon\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformRestCommon\Input\BaseParser;
use eZ\Publish\Core\REST\Client\Values\ViewResult as ViewResultValue;

/**
 * Parser for View.
 */
class ViewResult extends BaseParser
{
    /**
     * Parses input structure to a RestViewInput struct.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \EzSystems\EzPlatformRestCommon\Exceptions\Parser
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestViewInput
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $valueData = [];

        if (!array_key_exists('searchHits', $data) || !is_array($data['searchHits'])) {
            throw new Exceptions\Parser("Missing or invalid SearchHits in ViewResult data");
        }

        if (!array_key_exists('searchHit', $data['searchHits']) || !is_array($data['searchHits']['searchHit'])) {
            throw new Exceptions\Parser("Missing or invalid searchHits.searchHit in ViewResult data");
        }

        $valueData['searchHits'] = [];
        foreach ($data['searchHits']['searchHit'] as $searchHit) {
            $valueData['searchHits'][] = $parsingDispatcher->parse($searchHit, $searchHit['_media-type']);
        }

        return new ViewResultValue($valueData);
    }
}
