<?php
/**
 * File containing the ezp\Content\Criteria\SortByFieldClause class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package
 */

namespace ezp\Content\Criteria;

/**
 * This class implements query sorting on a content field
 * @package ezp
 * @subpackage Content_criteria
 */
class SortByFieldClause extends SortByClause
{
    /**
     * Sort field
     * @var string
     */
    public $field;

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
}
?>
