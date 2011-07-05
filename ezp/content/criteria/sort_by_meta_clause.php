<?php
/**
 * File containing the ezp\content\Criteria\SortByMetaClause class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package
 */

namespace ezp\content\Criteria;

/**
 * This class implements query sorting on a meta field
 * @package ezp
 * @subpackage Content_criteria
 */
class SortByMetaClause extends SortByClause
{
    /**
     * Creates a new sort clause on $meta in $order order
     *
     * @param string $item A meta identifier to sort on
     * @param int $item
     */
    public function __construct( $meta, $order = self::ASC )
    {
        $this->meta = $meta;
        parent::__construct( $order );
    }

    /**
     * Sort meta field
     * @var string
     */
    public $meta;
}
?>