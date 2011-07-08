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

/**
 * @package ezp.Persistence.Content.Criterion
 */
class FullText extends Criterion
{
    /**
     * Creates a FullText criterion on $text
     *
     * @param string $text
     */
    public function __construct( $text )
    {
        $this->text = $text;
    }

    /**
     * The text to match against
     * @var string
     */
    public $text;
}
?>
