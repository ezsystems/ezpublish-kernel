<?php

/**
 * File containing the LocationRemoteId Criterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Matcher\LocationRemoteId as LocationRemoteIdCriterion;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Parser for LocationRemoteId Criterion.
 */
class LocationRemoteId extends BaseParser
{
    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return LocationRemoteIdCriterion
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('LocationRemoteIdCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <LocationRemoteIdCriterion> format');
        }

        return new LocationRemoteIdCriterion(explode(',', $data['LocationRemoteIdCriterion']));
    }
}
