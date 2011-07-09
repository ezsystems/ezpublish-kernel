<?php
/**
 * File containing the ezp\Content\Criteria\SortByMetaClause class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package
 */

namespace ezp\Content\Criteria;

/**
 * This class implements query sorting on a meta field
 * @package ezp
 * @subpackage Content_criteria
 */
class SortByMetaClause extends SortByClause
{
    /**
     * Sort meta field
     * @var string
     */
    public $meta;

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
}
?>
