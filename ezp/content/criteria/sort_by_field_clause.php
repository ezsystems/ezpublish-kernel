<?php
/**
 * File containing the ezp\content\Criteria\SortByFieldClause class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package
 */

namespace ezp\content\Criteria;

/**
 * This class implements query sorting on a content field
 * @package ezp
 * @subpackage Content_criteria
 */
class SortByFieldClause extends SortByClause
{
    /**
     * Creates a new sort clause on $field in $order order
     *
     * @param string $field A field identifier to sort on
     * @param int $order either self::ASC or self::DESC
     */
    public function __construct( $field, $order = self::ASC )
    {
        $this->field = $field;
        parent::__construct( $order );
    }

    /**
     * Sort field
     * @var string
     */
    public $field;
}
?>