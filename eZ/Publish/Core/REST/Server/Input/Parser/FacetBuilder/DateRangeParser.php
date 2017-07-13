<?php

/**
 * File containing the facet builder DateRange parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\DateRangeFacetBuilder;

/**
 * Parser for DateRange facet builder.
 */
class DateRangeParser extends BaseParser
{
    /**
     * Parses input structure to a FacetBuilder object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\DateRangeFacetBuilder
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        throw new Exceptions\Parser('<DateRange> is not supported yet');
        /* @todo: DateRangeFacetBuilder is an abstract class and has no descendants (?)

        if (!array_key_exists('DateRange', $data)) {
            throw new Exceptions\Parser('Invalid <DateRange> format');
        }

        $selectType = [
            'CREATED' => DateRangeFacetBuilder::CREATED,
            'MODIFIED' => DateRangeFacetBuilder::MODIFIED,
            'PUBLISHED' => DateRangeFacetBuilder::PUBLISHED,
        ];

        if (isset($data['DateRange']['select'])) {
            $type = $data['DateRange']['select'];

            if (!isset($selectType[$type])) {
                throw new Exceptions\Parser('<DateRange> unknown type (supported: '.implode (', ', array_keys($selectType)).')');
            }

            $data['type'] = DateRangeFacetBuilder::$type;

            unset($data['DateRange']['select']);
        }

        return new DateRangeFacetBuilder($data['DateRange']);
        */
    }
}
