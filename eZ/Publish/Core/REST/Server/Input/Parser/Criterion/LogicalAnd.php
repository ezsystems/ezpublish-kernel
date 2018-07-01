<?php

/**
 * File containing the LogicalAnd Criterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\Core\REST\Server\Input\Parser\Criterion as CriterionParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as LogicalAndCriterion;

/**
 * Parser for LogicalAnd Criterion.
 */
class LogicalAnd extends CriterionParser
{
    /**
     * Parses input structure to a LogicalAnd Criterion object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('AND', $data) && !is_array($data['AND'])) {
            throw new Exceptions\Parser('Invalid <AND> format');
        }

        $criteria = array();
        foreach ($data['AND'] as $criterionName => $criterionData) {
            if (is_array($criterionData) && !array_key_exists(0, $criterionData)) {
                $criterionName = key($criterionData);
                $criterionData = current($criterionData);
            }
            $criteria[] = $this->dispatchCriterion($criterionName, $criterionData, $parsingDispatcher);
        }

        return new LogicalAndCriterion($criteria);
    }
}
