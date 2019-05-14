<?php

/**
 * File containing the LocationRemoteId Criterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use EzSystems\EzPlatformRest\Input\BaseParser;
use EzSystems\EzPlatformRest\Input\ParsingDispatcher;
use EzSystems\EzPlatformRest\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationRemoteId as LocationRemoteIdCriterion;

/**
 * Parser for LocationRemoteId Criterion.
 */
class LocationRemoteId extends BaseParser
{
    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRest\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \EzSystems\EzPlatformRest\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationRemoteId
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('LocationRemoteIdCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <LocationRemoteIdCriterion> format');
        }

        return new LocationRemoteIdCriterion(explode(',', $data['LocationRemoteIdCriterion']));
    }
}
