<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to perform a query
 *
 * @property $criterion Deprecated alias for $query
 */
class Query extends ValueObject
{
    const SORT_ASC = 'ascending';

    const SORT_DESC = 'descending';

    /**
     * The Query filter
     *
     * Can contain multiple criterion, as items of a logical one (by default
     * AND)
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public $filter;

    /**
     * The Query query
     *
     * Can contain multiple criterion, as items of a logical one (by default
     * AND). Defaults to MatchAll.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public $query;

    /**
     * Query sorting clauses
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    public $sortClauses = array();

    /**
     * An array of facet builders
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[]
     */
    public $facetBuilders = array();

    /**
     * Query offset
     *
     * @var int
     */
    public $offset = 0;

    /**
     * Query limit
     *
     * @var int
     */
    public $limit;

    /**
     * If true spellcheck suggestions are returned
     *
     * @var boolean
     */
    public $spellcheck;

    /**
     * Wrapper for deprecated $criterion property
     *
     * @param string $property
     * @return mixed
     */
    public function __get( $property)
    {
        if ( $property === 'criterion' )
        {
            return $this->query;
        }

        return parent::__get( $property );
    }

    /**
     * Wrapper for deprecated $criterion property
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public function __set( $property, $value )
    {
        if ( $property === 'criterion' )
        {
            $this->query = $value;
            return;
        }

        return parent::__set( $property, $value );
    }

    /**
     * Wrapper for deprecated $criterion property
     *
     * @param string $property
     * @return bool
     */
    public function __isset( $property )
    {
        if ( $property === 'criterion' )
        {
            return true;
        }

        return parent::__isset( $property );
    }
}
