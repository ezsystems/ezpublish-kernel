<?php
/**
 * File containing the ViewInput parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\Criterion as CriterionParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\REST\Server\Values\RestViewInput;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as LogicalAndCriterion;

/**
 * Parser for ViewInput
 */
class ViewInput extends CriterionParser
{
    /**
     * Construct
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     */
    public function __construct( UrlHandler $urlHandler )
    {
        parent::__construct( $urlHandler );
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
                $query->criterion = $criteria[0];
            }
            else
            {
                $query->criterion = new LogicalAndCriterion( $criteria );
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
        if ( array_key_exists( 'SortClauses', $queryData ) )
        {
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
