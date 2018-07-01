<?php

/**
 * File containing the ContentIdCriterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserMetadata as UserMetadataCriterion;

/**
 * Parser for ViewInput.
 */
class UserMetadata extends BaseParser
{
    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserMetadata
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!isset($data['UserMetadataCriterion'])) {
            throw new Exceptions\Parser('Invalid <UserMetadataCriterion> format');
        }

        if (!isset($data['UserMetadataCriterion']['Target'])) {
            throw new Exceptions\Parser('Invalid <Target> format');
        }

        $target = $data['UserMetadataCriterion']['Target'];

        if (!isset($data['UserMetadataCriterion']['Value'])) {
            throw new Exceptions\Parser('Invalid <Value> format');
        }

        if (!in_array(gettype($data['UserMetadataCriterion']['Value']), ['integer', 'string', 'array'])) {
            throw new Exceptions\Parser('Invalid <Value> format');
        }

        $value = is_array($data['UserMetadataCriterion']['Value'])
            ? $data['UserMetadataCriterion']['Value']
            : explode(',', $data['UserMetadataCriterion']['Value']);

        return new UserMetadataCriterion($target, null, $value);
    }
}
