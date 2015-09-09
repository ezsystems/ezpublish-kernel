<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Server\Input\Parser\Criterion as CriterionParser;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as LogicalAndCriterion;

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
     * @return \eZ\Publish\Core\REST\Server\Values\RestViewInput
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $query = $this->buildQuery();

        // Criteria
        // -- FullTextCriterion
        if (array_key_exists('Criteria', $data) && is_array($data['Criteria'])) {
            $criteria = array();
            foreach ($data['Criteria'] as $criterionName => $criterionData) {
                $criteria[] = $this->dispatchCriterion($criterionName, $criterionData, $parsingDispatcher);
            }

            if (count($criteria) === 1) {
                $query->filter = $criteria[0];
            } else {
                $query->filter = new LogicalAndCriterion($criteria);
            }
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
        // -- SortClause
        // ---- SortField
        if (array_key_exists('SortClauses', $data)) {
        }

        // FacetBuilders
        // -- contentTypeFacetBuilder
        if (array_key_exists('FacetBuilders', $data)) {
        }

        return $query;
    }

    /**
     * Builds and returns the Query (Location or Content object).
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    abstract protected function buildQuery();
}
