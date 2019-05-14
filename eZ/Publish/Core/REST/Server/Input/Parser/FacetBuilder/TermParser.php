<?php

/**
 * File containing the facet builder Term parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder;

use EzSystems\EzPlatformRest\Input\BaseParser;
use EzSystems\EzPlatformRest\Input\ParsingDispatcher;
use EzSystems\EzPlatformRest\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\TermFacetBuilder;

/**
 * Parser for Term facet builder.
 */
class TermParser extends BaseParser
{
    /**
     * Parses input structure to a FacetBuilder object.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRest\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \EzSystems\EzPlatformRest\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\TermFacetBuilder
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('Term', $data)) {
            throw new Exceptions\Parser('Invalid <Term> format');
        }

        return new TermFacetBuilder($data['Term']);
    }
}
