<?php

/**
 * File containing the LogicalOr Criterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\Core\REST\Server\Input\Parser\Criterion as CriterionParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values;

/**
 * Parser for LogicalOr Criterion.
 */
class LogicalOr extends CriterionParser
{
    /**
     * @var string
     */
    const TAG_NAME = 'OR';

    /**
     * Parses input structure to a LogicalOr Criterion object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists(static::TAG_NAME, $data) || !is_array($data[static::TAG_NAME])) {
            throw new Exceptions\Parser('Invalid <' . static::TAG_NAME . '> format');
        }

        $criteria = array();

        $flattenedCriteriaElements = $this->getFlattenedCriteriaData($data[static::TAG_NAME]);
        foreach ($flattenedCriteriaElements as $criterionElement) {
            $criteria[] = $this->dispatchCriterion(
                $criterionElement['type'],
                $criterionElement['data'],
                $parsingDispatcher
            );
        }

        return new Values\Content\Query\Criterion\LogicalOr($criteria);
    }

    /**
     * @param array $criteriaByType
     * @return array
     */
    protected function getFlattenedCriteriaData(array $criteriaByType)
    {
        $criteria = [];
        foreach ($criteriaByType as $type => $criterion) {
            if (is_array($criterion) && $this->isNumericArray($criterion)) {
                foreach ($criterion as $criterionElement) {
                    $criteria[] = [
                        'type' => $type,
                        'data' => $criterionElement,
                    ];
                }
            } else {
                $criteria[] = [
                    'type' => $type,
                    'data' => $criterion,
                ];
            }
        }

        return $criteria;
    }

    /**
     * Checks if the given $value is a purely numeric array.
     *
     * @param array $value
     *
     * @return bool
     */
    protected function isNumericArray(array $value)
    {
        foreach (array_keys($value) as $key) {
            if (is_string($key)) {
                return false;
            }
        }

        return true;
    }
}
