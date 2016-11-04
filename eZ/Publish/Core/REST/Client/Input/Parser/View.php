<?php

/**
 * File containing the ViewInput parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Client\Values\View as ViewValue;

/**
 * Parser for View.
 */
class View extends BaseParser
{
    /**
     * Parses input structure to a RestViewInput struct.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestViewInput
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $viewData = [];

        // identifier
        if (!array_key_exists('identifier', $data)) {
            throw new Exceptions\Parser('Missing identifier attribute for ViewInput.');
        }
        $viewData['identifier'] = $data['identifier'];

        // query
        if (array_key_exists('ContentQuery', $data) && is_array($data['ContentQuery'])) {
            $viewData['query'] = $parsingDispatcher->parse($data['Query'], 'application/vnd.ez.api.internal.ContentQuery');
        }

        elseif (array_key_exists('LocationQuery', $data) && is_array($data['LocationQuery'])) {
            $viewData['query'] = $parsingDispatcher->parse($data['LocationQuery'], 'application/vnd.ez.api.internal.LocationQuery');
        }

        elseif (array_key_exists('Query', $data) && is_array($data['Query'])) {
            $viewData['query'] = $parsingDispatcher->parse($data['Query'], 'application/vnd.ez.api.internal.ContentQuery');
        }

        else {
            throw new Exceptions\Parser('Missing or invalid LocationQuery or ContentQuery attribute for View.');
        }

        // results
        $viewData['result'] = $parsingDispatcher->parse($data['Result'], $data['Result']['_media-type']);

        return new ViewValue($viewData);
    }
}
