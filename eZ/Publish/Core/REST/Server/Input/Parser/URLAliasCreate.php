<?php

/**
 * File containing the URLAliasCreate parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Parser for URLAliasCreate.
 */
class URLAliasCreate extends BaseParser
{
    /**
     * Parser tools.
     *
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct.
     *
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
     * @return array
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('_type', $data)) {
            throw new Exceptions\Parser("Missing '_type' value for URLAliasCreate.");
        }

        if ($data['_type'] == 'LOCATION') {
            if (!array_key_exists('location', $data)) {
                throw new Exceptions\Parser("Missing 'location' value for URLAliasCreate.");
            }

            if (!is_array($data['location']) || !array_key_exists('_href', $data['location'])) {
                throw new Exceptions\Parser("Missing 'location' > '_href' attribute for URLAliasCreate.");
            }
        } else {
            if (!array_key_exists('resource', $data)) {
                throw new Exceptions\Parser("Missing 'resource' value for URLAliasCreate.");
            }
        }

        if (!array_key_exists('path', $data)) {
            throw new Exceptions\Parser("Missing 'path' value for URLAliasCreate.");
        }

        if (!array_key_exists('languageCode', $data)) {
            throw new Exceptions\Parser("Missing 'languageCode' value for URLAliasCreate.");
        }

        if (array_key_exists('alwaysAvailable', $data)) {
            $data['alwaysAvailable'] = $this->parserTools->parseBooleanValue($data['alwaysAvailable']);
        } else {
            $data['alwaysAvailable'] = false;
        }

        if (array_key_exists('forward', $data)) {
            $data['forward'] = $this->parserTools->parseBooleanValue($data['forward']);
        } else {
            $data['forward'] = false;
        }

        return $data;
    }
}
