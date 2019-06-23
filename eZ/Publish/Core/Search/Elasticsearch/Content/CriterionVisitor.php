<?php

/**
 * File containing the base CriterionVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use RuntimeException;

/**
 * Visits the criterion tree into a hash representation of Elasticsearch query/filter AST.
 *
 * @deprecated
 */
abstract class CriterionVisitor
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    abstract public function canVisit(Criterion $criterion);

    /**
     * Map field value to a proper Elasticsearch filter representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed Hash representation of Elasticsearch filter abstract syntax tree
     */
    abstract public function visitFilter(
        Criterion $criterion,
        CriterionVisitorDispatcher $dispatcher,
        array $languageFilter
    );

    /**
     * Map field value to a proper Elasticsearch query representation.
     *
     * By default this method fallbacks on {@link self::visitFilter()}, override it as needed.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed Hash representation of Elasticsearch query abstract syntax tree
     */
    public function visitQuery(Criterion $criterion, CriterionVisitorDispatcher $dispatcher, array $languageFilter)
    {
        return $this->visitFilter($criterion, $dispatcher, $languageFilter);
    }

    /**
     * Get Elasticsearch range query.
     *
     * Start and end are optional, depending on the respective operator. Pass
     * null in this case. The operator may be one of:
     *
     * - case Operator::GT:
     * - case Operator::GTE:
     * - case Operator::LT:
     * - case Operator::LTE:
     * - case Operator::BETWEEN:
     *
     * @throws \RuntimeException If operator is no recognized
     *
     * @param mixed $operator
     * @param mixed $start
     * @param mixed $end
     *
     * @return string
     */
    protected function getQueryRange($operator, $start, $end)
    {
        $start = $this->prepareValue($start);
        $end = $this->prepareValue($end);

        $startBrace = '[';
        $startValue = '*';
        $endValue = '*';
        $endBrace = ']';

        switch ($operator) {
            case Operator::GT:
                $startBrace = '{';
                $endBrace = '}';
                // Intentionally omitted break

            case Operator::GTE:
                $startValue = $start;
                break;

            case Operator::LT:
                $startBrace = '{';
                $endBrace = '}';
                // Intentionally omitted break

            case Operator::LTE:
                $endValue = $end;
                break;

            case Operator::BETWEEN:
                $startValue = $start;
                $endValue = $end;
                break;

            default:
                throw new \RuntimeException("Unknown operator: $operator");
        }

        return "$startBrace$startValue TO $endValue$endBrace";
    }

    /**
     * Get Elasticsearch range filter.
     *
     * Start and end are optional, depending on the respective operator. Pass
     * null in this case. The operator may be one of:
     *
     * - case Operator::GT:
     * - case Operator::GTE:
     * - case Operator::LT:
     * - case Operator::LTE:
     * - case Operator::BETWEEN:
     *
     * @throws \RuntimeException If operator is no recognized
     *
     * @param mixed $operator
     * @param mixed $start
     * @param mixed $end
     *
     * @return string
     */
    protected function getFilterRange($operator, $start, $end)
    {
        if (($operator === Operator::LT) || ($operator === Operator::LTE)) {
            $end = $start;
            $start = null;
        }

        switch ($operator) {
            case Operator::GT:
                $range = [
                    'gt' => $start,
                ];
                break;

            case Operator::GTE:
                $range = [
                    'gte' => $start,
                ];
                break;

            case Operator::LT:
                $range = [
                    'lt' => $end,
                ];
                break;

            case Operator::LTE:
                $range = [
                    'lte' => $end,
                ];
                break;

            case Operator::BETWEEN:
                $range = [
                    'gte' => $start,
                    'lte' => $end,
                ];
                break;

            default:
                throw new RuntimeException("Unknown operator '{$operator}'");
        }

        return $range;
    }

    /**
     * Converts given $value to the appropriate Elasticsearch representation.
     *
     * The value will be converted to string representation and escaped if needed.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function prepareValue($value)
    {
        switch (gettype($value)) {
            case 'boolean':
                return $value ? 'true' : 'false';

            case 'string':
                return '"' . preg_replace('/("|\\\)/', '\\\$1', $value) . '"';

            default:
                return (string)$value;
        }
    }
}
