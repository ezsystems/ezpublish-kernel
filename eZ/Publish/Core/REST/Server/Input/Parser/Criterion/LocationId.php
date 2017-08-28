<?php

/**
 * File containing the LocationId Criterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Matcher\LocationId as LocationIdCriterion;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Parser for LocationId Criterion.
 */
class LocationId extends BaseParser
{
    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return LocationIdCriterion
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('LocationIdCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <LocationIdCriterion> format');
        }

        return new LocationIdCriterion(explode(',', $data['LocationIdCriterion']));
    }
}
