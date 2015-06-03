<?php
/**
 * File containing the ViewInput parser class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\Criterion as CriterionParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\REST\Server\Values\RestViewInput;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as LogicalAndCriterion;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Parser for ViewInput
 */
class ViewInput extends CriterionParser
{
    /**
     * Location service
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * Parser tools
     *
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct( LocationService $locationService, ParserTools $parserTools )
    {
        $this->locationService = $locationService;
        $this->parserTools = $parserTools;
    }

    /**
     * Parses input structure to a RestViewInput struct
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestViewInput
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $restViewInput = new RestViewInput();

        // identifier
        if ( !array_key_exists( 'identifier', $data ) )
        {
            throw new Exceptions\Parser( "Missing <identifier> attribute for <ViewInput>." );
        }
        $restViewInput->identifier  = $data['identifier'];

        // query
        if ( !array_key_exists( 'Query', $data ) || !is_array( $data['Query'] ) )
        {
            throw new Exceptions\Parser( "Missing <Query> attribute for <ViewInput>." );
        }

        $query = new Query();
        $queryData = $data['Query'];

        // Criteria
        // -- FullTextCriterion
        if ( array_key_exists( 'Criteria', $queryData ) && is_array( $queryData['Criteria'] ) )
        {
            $criteria = array();
            foreach ( $queryData['Criteria'] as $criterionName => $criterionData )
            {
                $criteria[] = $this->dispatchCriterion( $criterionName, $criterionData, $parsingDispatcher );
            }

            if ( count( $criteria ) === 1 )
            {
                $query->filter = $criteria[0];
            }
            else
            {
                $query->filter = new LogicalAndCriterion( $criteria );
            }
        }

        // limit
        if ( array_key_exists( 'limit', $queryData ) )
        {
            $query->limit = (int)$queryData['limit'];
        }

        // offset
        if ( array_key_exists( 'offset', $queryData ) )
        {
            $query->offset = (int)$queryData['offset'];
        }

        // SortClauses
        // -- SortClause
        // ---- SortField
        // ---- SortOrder
        if ( array_key_exists( 'SortClauses', $queryData ) && is_array( $queryData['SortClauses'] ) )
        {
             foreach ( $queryData['SortClauses'] as $sortClause )
             {
                 if ( array_key_exists( 'SortField', $sortClause ) )
                 {
                     $sortField = $this->parserTools->parseDefaultSortField( $sortClause['SortField'] );

                     $sortOrder = Location::SORT_ORDER_ASC;
                     if ( array_key_exists( 'SortOrder', $sortClause ) )
                     {
                         $sortOrder = $this->parserTools->parseDefaultSortOrder( $sortClause['SortOrder'] );
                     }

                     $query->sortClauses[] = $this->locationService->getSortClauseBySortField( $sortField, $sortOrder );
                 }
             }
        }

        // FacetBuilders
        // -- contentTypeFacetBuilder
        if ( array_key_exists( 'FacetBuilders', $queryData ) )
        {
        }

        $restViewInput->query = $query;

        return $restViewInput;
    }
}
