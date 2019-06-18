<?php

/**
 * File containing the ParentLocationRemoteId Criterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId as ParentLocationIdCriterion;

/**
 * Parser for ParentLocationId Criterion.
 */
class ParentLocationRemoteId extends BaseParser
{
    /**
     * Location service.
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('ParentLocationRemoteIdCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <ParentLocationRemoteIdCriterion> format');
        }
        $contentIdArray = [];
        foreach (explode(',', $data['ParentLocationRemoteIdCriterion']) as $parentRemoteId) {
            $location = $this->locationService->loadLocationByRemoteId($parentRemoteId);
            $contentIdArray[] = $location->id;
        }

        return new ParentLocationIdCriterion($contentIdArray);
    }
}
