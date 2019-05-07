<?php

/**
 * File containing the ViewInput parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher;
use EzSystems\EzPlatformRestCommon\Exceptions;
use eZ\Publish\Core\REST\Server\Values\RestViewInput;
use EzSystems\EzPlatformRestCommon\Input\BaseParser;

/**
 * Parser for ViewInput.
 */
class ViewInput extends BaseParser
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
        $restViewInput = new RestViewInput();

        // identifier
        if (!array_key_exists('identifier', $data)) {
            throw new Exceptions\Parser('Missing <identifier> attribute for <ViewInput>.');
        }
        $restViewInput->identifier = $data['identifier'];

        // query
        if (!array_key_exists('Query', $data) || !is_array($data['Query'])) {
            throw new Exceptions\Parser('Missing <Query> attribute for <ViewInput>.');
        }

        $restViewInput->query = $parsingDispatcher->parse($data['Query'], 'application/vnd.ez.api.internal.ContentQuery');

        return $restViewInput;
    }
}
