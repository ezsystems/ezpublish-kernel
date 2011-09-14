<?php
/**
 * File containing the Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType;

/**
 * Description of Value
 */
interface Value
{
    /**
     * Initializes the field value with $state array.
     * @todo Handle serialization with var_export() ?
     */
    public function __set_state( array $state );
}
