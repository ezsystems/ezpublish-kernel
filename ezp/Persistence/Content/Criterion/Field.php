<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\Field class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */


namespace ezp\Persistence\Content\Criterion;

/**
 * @package ezp.Persistence.Content.Criterion
 */
class Field extends Criterion
{
    /**
     * Creates a new Field Criterion.
     * Matches $fieldIdentifier against $matchValue using $operator
     *
     * @param string $fieldIdentifer
     * @param string $operator
     * @param string $matchValue
     */
    public function __construct( $fieldIdentifier, $operator, $matchValue )
    {
        $this->fieldIdentifier = $fieldIdentifier;
        $this->opera = $operator;
        $this->matchValue = $matchValue;
    }

    /**
     * The ContentField identifier
     * @var string
     */
    public $fieldIdentifier;

    /**
     * The value $fieldIdentifier should be matched against
     * @var mixed
     */
    public $matchValue;
}
?>
