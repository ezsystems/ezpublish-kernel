<?php

/**
 * File containing the facet builder Section parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\SectionFacetBuilder;

/**
 * Parser for Section facet builder.
 */
class SectionParser extends BaseParser
{
    /**
     * Parses input structure to a FacetBuilder object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\SectionFacetBuilder
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('Section', $data)) {
            throw new Exceptions\Parser('Invalid <Section> format');
        }

        return new SectionFacetBuilder($data['Section']);
    }
}
