<?php
/**
 * File containing the FieldStub class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\Values\Content\Field}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\Field
 */
class FieldStub extends Field
{
    /**
     * Allows to set the value
     *
     * @param mixed $value
     *
     * @return void
     */
    public function setValue( $value )
    {
        $this->value = $value;
    }
}
