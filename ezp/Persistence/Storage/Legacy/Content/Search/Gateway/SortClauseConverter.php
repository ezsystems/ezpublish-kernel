<?php
/**
 * File containing the EzcDatabase sort clause converter class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Search\Gateway;
use ezp\Persistence\Storage\Legacy\Content\Search\Gateway,
    ezp\Persistence\Content\Query\SortClause;

/**
 * Converter manager for sort clauses
 */
class SortClauseConverter
{
    /**
     * Sort cluase handlers
     *
     * @var array(SortClauseHandler)
     */
    protected $handler;

    /**
     * Construct from an optional array of sort clause handlers
     *
     * @param array $handler
     * @return void
     */
    public function __construct( array $handler = array() )
    {
        $this->handler = $handler;
    }

    /**
     * Apply sort clauses to query
     *
     * @param \ezcQuerySelect $query
     * @param array $sortClauses
     * @return void
     */
    public function apply( \ezcQuerySelect $query, array $sortClauses )
    {
    }
}

