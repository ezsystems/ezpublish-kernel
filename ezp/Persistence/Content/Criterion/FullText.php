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
    ezp\Persistence\Content\Criterion\Operator\Specifications,
    ezp\Persistence\Content\Interfaces\Criterion as CriterionInterface;

/**
 */
class FullText extends Criterion implements CriterionInterface
{
    /**
     * Creates a FullText criterion on $text
     *
     * @param string $target Not used
     * @param string $operator Not used
     * @param string $text The text to match on
     */
    public function __construct( $target, $operator, $value )
    {
        parent::__construct( $target, $operator, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications( Operator::LIKE, Specifications::FORMAT_SINGLE )
        );
    }
}
?>
