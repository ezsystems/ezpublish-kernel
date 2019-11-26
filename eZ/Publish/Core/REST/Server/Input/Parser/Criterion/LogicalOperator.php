<?php

/**
 * File containing the LogicalOperator Criterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

/**
 * Parser for LogicalOperator Criterion.
 */
class LogicalOperator extends Criterion
{
    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        throw new \Exception('@todo implement');
    }

    /**
     * @param array $criteriaByType
     * @return array
     */
    protected function getFlattenedCriteriaData(array $criteriaByType)
    {
        if ($this->isZeroBasedArray($criteriaByType)) {
            $criteriaByType = $this->normalizeCriteriaByType($criteriaByType);
        }

        $criteria = [];
        foreach ($criteriaByType as $type => $criterion) {
            if (!is_array($criterion) || !$this->isZeroBasedArray($criterion)) {
                $criterion = [$criterion];
            }

            foreach ($criterion as $criterionElement) {
                $criteria[] = [
                    'type' => $type,
                    'data' => $criterionElement,
                ];
            }
        }

        return $criteria;
    }

    /**
     * Checks if the given $value is zero based.
     *
     * @param array $value
     *
     * @return bool
     */
    protected function isZeroBasedArray(array $value)
    {
        reset($value);

        return empty($value) || key($value) === 0;
    }

    /**
     * Normalize list of criteria to be provided as the expected criterion type to its value map.
     *
     * Changes:
     * <code>
     * [
     *  0 => "CriterionType1" => "<value1>",
     *  1 => "CriterionType1" => "<value2>",
     *  2 => "CriterionType2" => "<value3>",
     * ]
     * </code>
     * into:
     * <code>
     * [
     *  "CriterionType1" => ["<value1>", "<value2>"],
     *  "CriterionType2" => ["<value3>"],
     * ]
     * </code>
     *
     * @param array $criterionList zero-based list of criteria
     *
     * @return array map of criterion types to their values
     */
    private function normalizeCriteriaByType(array $criterionList)
    {
        $criteriaByType = [];
        foreach ($criterionList as $criterion) {
            foreach ($criterion as $criterionType => $value) {
                if (!isset($criteriaByType[$criterionType])) {
                    $criteriaByType[$criterionType] = [];
                }

                $criteriaByType[$criterionType] = array_merge(
                    $criteriaByType[$criterionType],
                    !is_array($value) ? [$value] : $value
                );
            }
        }

        return $criteriaByType;
    }
}
