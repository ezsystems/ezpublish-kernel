<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\FullText
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Interfaces\Criterion as CriterionInterface;

/**
 */
class FullText extends Criterion implements CriterionInterface
{
    /**
     * Creates a FullText criterion on $text
     *
     * @param string $text
     */
    public function __construct( $target, $operator, $text )
    {
        $this->value = $value;
        $this->operator = Operator::LIKE;

        // @todo Not required ?
        $this->target = null;
    }

    /**
     * The text to match against
     * @var string
     */
    public $value;
}
?>
