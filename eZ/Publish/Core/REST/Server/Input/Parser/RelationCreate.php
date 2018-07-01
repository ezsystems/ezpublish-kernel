<?php

/**
 * File containing the RelationCreate parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Parser for RelationCreate.
 */
class RelationCreate extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return mixed
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('Destination', $data) || !is_array($data['Destination'])) {
            throw new Exceptions\Parser("Missing or invalid 'Destination' element for RelationCreate.");
        }

        if (!array_key_exists('_href', $data['Destination'])) {
            throw new Exceptions\Parser("Missing '_href' attribute for Destination element in RelationCreate.");
        }

        return $this->requestParser->parseHref($data['Destination']['_href'], 'contentId');
    }
}
