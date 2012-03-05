<?php
/**
 * File containing the ezp\Content\Query class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 */

namespace ezp\Content;

class Query
{
    /**
     * The Query criterion
     * Can contain multiple criterion, as items of a logical one (by default AND)
     * @var \eZ\Publish\SPI\Persistence\Content\Query\Criterion
     */
    public $criterion;

    /**
     * Query sorting clauses
     * @var \eZ\Publish\SPI\Persistence\Content\Query\SortClause[]
     */
    public $sortClauses;

    /**
     * Query offset
     * @var integer
     */
    public $offset;

    /**
     * Query limit
     * @var integer
     */
    public $limit;

    const SORT_ASC = 'ascending';

    const SORT_DESC = 'descending';
}
