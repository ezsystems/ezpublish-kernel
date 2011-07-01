<?php
/**
 * File containing the ezp\content\Criteria\SortByClause abstract class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package
 */

namespace ezp\content\Criteria;

/**
 * This class is the base for sortBy containers of Content queries
 * @package ezp
 * @subpackage Content_criteria
 */

abstract class SortByClause
{
    /**
     * Creates a new sort clause on $item in $order order
     *
     * @param string $item
     * @param int $item
     */
    public function __construct( $item, $order = self::ASC )
    {
        if ( $order != self::ASC && $order != self::DESC )
        {
            throw new \InvalidArgumentException( "\$order must be one of SortByClause::ASC or SortByClause::DESC" );
        }

        $this->item = $item;
        $this->order = $order;
    }

    const ASC = 1;
    const DESC = 2;

    /**
     * @var string
     */
    public $item;

    /**
     * @var int
     */
    public $order;
}
?>