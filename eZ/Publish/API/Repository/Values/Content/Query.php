<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to perform a query
 */
class Query extends ValueObject
{
    const SORT_ASC = 'ascending';

    const SORT_DESC = 'descending';

    /**
     * The Query criterion
     * Can contain multiple criterion, as items of a logical one (by default AND)
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public $criterion;

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
}
