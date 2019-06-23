<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Server\Input\Parser\Criterion as CriterionParser;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion as CriterionValue;

/**
 * Content/Location Query Parser.
 */
abstract class Query extends CriterionParser
{
    /**
     * Parses input structure to a Query.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $query = $this->buildQuery();

        // @deprecated Criteria
        // -- FullTextCriterion
        if (array_key_exists('Criteria', $data) && is_array($data['Criteria'])) {
            $message = 'The Criteria element is deprecated since ezpublish-kernel 6.6, and will be removed in 8.0. Use Filter instead, or Query for criteria that should affect scoring.';
            if (array_key_exists('Filter', $data) && is_array($data['Filter'])) {
                $message .= ' The Criteria element will be merged into Filter.';
                $data['Filter'] = array_merge($data['Filter'], $data['Criteria']);
            } else {
                $data['Filter'] = $data['Criteria'];
            }

            @trigger_error($message, E_USER_DEPRECATED);
        }

        if (array_key_exists('Filter', $data) && is_array($data['Filter'])) {
            $query->filter = $this->processCriteriaArray($data['Filter'], $parsingDispatcher);
        }

        if (array_key_exists('Query', $data) && is_array($data['Query'])) {
            $query->query = $this->processCriteriaArray($data['Query'], $parsingDispatcher);
        }

        // limit
        if (array_key_exists('limit', $data)) {
            $query->limit = (int)$data['limit'];
        }

        // offset
        if (array_key_exists('offset', $data)) {
            $query->offset = (int)$data['offset'];
        }

        // SortClauses
        // -- [SortClauseName: direction|data]
        if (array_key_exists('SortClauses', $data)) {
            $query->sortClauses = $this->processSortClauses($data['SortClauses'], $parsingDispatcher);
        }

        // FacetBuilders
        // -- facetBuilderListType
        if (array_key_exists('FacetBuilders', $data)) {
            $facetBuilders = [];
            foreach ($data['FacetBuilders'] as $facetBuilderName => $facetBuilderData) {
                $facetBuilders[] = $this->dispatchFacetBuilder($facetBuilderName, $facetBuilderData, $parsingDispatcher);
            }
            $query->facetBuilders = $facetBuilders;
        }

        return $query;
    }

    /**
     * Builds and returns the Query (Location or Content object).
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    abstract protected function buildQuery();

    /**
     * @param array $criteriaArray
     * @param ParsingDispatcher $parsingDispatcher
     *
     * @return CriterionValue|null A criterion, or a LogicalAnd with a set of Criterion, or null if an empty array was given
     */
    private function processCriteriaArray(array $criteriaArray, ParsingDispatcher $parsingDispatcher)
    {
        if (count($criteriaArray) === 0) {
            return null;
        }

        $criteria = [];
        foreach ($criteriaArray as $criterionName => $criterionData) {
            $criteria[] = $this->dispatchCriterion($criterionName, $criterionData, $parsingDispatcher);
        }

        return (count($criteria) === 1) ? $criteria[0] : new CriterionValue\LogicalAnd($criteria);
    }

    /**
     * Handles SortClause data.
     *
     * @param array $sortClausesArray
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return array
     */
    private function processSortClauses(array $sortClausesArray, ParsingDispatcher $parsingDispatcher)
    {
        $sortClauses = [];
        foreach ($sortClausesArray as $sortClauseName => $sortClauseData) {
            if (!is_array($sortClauseData) || !isset($sortClauseData[0])) {
                $sortClauseData = [$sortClauseData];
            }

            foreach ($sortClauseData as $data) {
                $sortClauses[] = $this->dispatchSortClause($sortClauseName, $data, $parsingDispatcher);
            }
        }

        return $sortClauses;
    }
}
