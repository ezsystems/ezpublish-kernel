<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query as ApiQuery;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as LogicalAndCriterion;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Content/Location Query Parser.
 */
abstract class Query extends BaseParser
{
    /**
     * Builds and returns the Query (Location or Content object).
     * @return ApiQuery
     */
    abstract protected function buildQuery();

    /**
     * @var array
     */
    private $criterionIdMap = array(
        'AND' => 'LogicalAnd',
        'OR' => 'LogicalOr',
        'NOT' => 'LogicalNot',
    );

    private $sortClauseIdMap = array(
      'PATH' => 'eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location\Path',
      'MODIFIED' => 'eZ\Publish\API\Repository\Values\Content\Query\SortClause\DateModified',
      'CREATED' => '\eZ\Publish\API\Repository\Values\Content\Query\SortClause\DatePublished',
      'SECTIONIDENTIFER' => 'eZ\Publish\API\Repository\Values\Content\Query\SortClause\SectionIdentifier',
      'FIELD' => false,
      'PRIORITY' => 'eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location\Priority',
      'NAME' => '\eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentName',
    );

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
                $criteria[] = $parsingDispatcher->parse(
                    [$criterionName => $criterionData],
                    $this->getCriterionMediaType($criterionName)
                );
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
        if (array_key_exists('SortClauses', $data) && is_array($data['SortClauses'])) {
            $sortClauses = [];
            foreach ($data['SortClauses'] as $sortClauseInput) {
                $direction = isset($sortClauseInput['SortDirection']) ? $sortClauseInput['SortDirection'] : ApiQuery::SORT_ASC;
                $sortClauses[] = $this->buildSortClause($sortClauseInput['SortField'], $direction);
            }
            $query->sortClauses = $sortClauses;
        }

        // FacetBuilders
        // -- contentTypeFacetBuilder
        if (array_key_exists('FacetBuilders', $data)) {
        }

        return $query;
    }

    private function buildSortClause($name, $direction)
    {
        if (!isset($this->sortClauseIdMap[$name])) {
            throw new Exceptions\Parser("Unknown SortClause id $name");
        }

        if ($this->sortClauseIdMap[$name] === false) {
            throw new NotImplementedException('Field SortClause parser');
        }

        return new $this->sortClauseIdMap[$name]($direction);
    }

    protected function getCriterionMediaType($criterionName)
    {
        $criterionName = str_replace('Criterion', '', $criterionName);
        if (isset($this->criterionIdMap[$criterionName])) {
            $criterionName = $this->criterionIdMap[$criterionName];
        }

        return 'application/vnd.ez.api.internal.criterion.' . $criterionName;
    }
}
