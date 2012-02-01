<?php
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

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
     * @var Criterion
     */
    public $criterion;

    /**
     * Query sorting clauses
     *
     * @var array an array of {@link SortClause}
     */
    public $sortClauses;

    /**
     * Query offset
     *
     * @var integer
     */
    public $offset;

    /**
     * Query limit
     *
     * @var integer
     */
    public $limit;
}
