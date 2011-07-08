<?php
/**
 * File containing the ezp\Content\Criteria\SortByClause abstract class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package
 */

namespace ezp\Content\Criteria;

/**
 * This class is the base for sortBy containers of Content queries
 * @package ezp
 * @subpackage Content_criteria
 */

abstract class SortByClause
{
    /**
     * Creates a new sort clause in $order order
     *
     * Can be called by the children constructor as a helper in order to handle the $order parameter
     *
     * @param int $order
     */
    protected function __construct( $order = self::ASC )
    {
        if ( $order != self::ASC && $order != self::DESC )
        {
            throw new \InvalidArgumentException( "\$order must be one of SortByClause::ASC or SortByClause::DESC" );
        }

        $this->order = $order;
    }

    /**
     * Returns the sorting parameters this object stores
     *
     * @return @todo analyze
     */
    abstract public function getSortBy();

    /**
     * Sort order constants
     */
    const ASC = true;
    const DESC = false;

    /**
     * Sort order, one of self::ASC / self::DESC
     * @var bool
     */
    public $order;
}
?>