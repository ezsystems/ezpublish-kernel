<?php
/**
 * File containing Result collection class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace ezp\Content\Search;
use ezp\Base\Collection\Type as TypeCollection,
    ezp\Content\Query;

/**
 * Result collection class
 * Holds results returned by a search
 */
class Result extends TypeCollection
{
    /**
     * @var int Total count of result (might differ from collection count if offset and/or limit is used in query)
     */
    public $totalCount = 0;

    /**
     * @var \ezp\Content\Query Used to generate this result
     */
    public $query;

    /**
     * Constructor
     *
     * @param \ezp\Content[] $elements
     * @param int $totalCount
     * @param \ezp\Content\Query $query
     */
    public function __construct( array $elements, $totalCount, Query $query )
    {
        parent::__construct( 'ezp\\Content', $elements );
        $this->totalCount = $totalCount;
        $this->query = $query;
    }
}
