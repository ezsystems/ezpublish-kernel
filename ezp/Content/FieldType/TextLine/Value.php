<?php
/**
 * File containing the TextLine Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\TextLine;
use ezp\Content\FieldType\Value as ValueInterface;

/**
 * Value for TextLine field type
 */
class Value implements ValueInterface
{
    /**
     * Text content
     * @var string
     */
    public $text;

    /**
     * Construct a new Value object and initialize it $text
     * @param string $text
     */
    public function __construct( $text = '' )
    {
        $this->text = (string)$text;
    }

    public function __set_state( array $state )
    {
        $this->text = $state['text'];
    }
}
