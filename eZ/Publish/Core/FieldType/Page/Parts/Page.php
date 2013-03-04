<?php
/**
 * File containing the Page class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page\Parts;

use OutOfBoundsException;

class Page extends Base
{
    /**
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Zone[]
     */
    protected $zones = array();

    /**
     * Returns zone object by given $index.
     * Throws an OutOfBoundsException if no zone can be found à $index.
     *
     * @param int $index
     *
     * @throws \OutOfBoundsException
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Zone
     */
    public function getZone( $index )
    {
        if ( !isset( $this->zones[$index] ) )
            throw new OutOfBoundsException( "No zone at index #$index for current Page." );

        return $this->zones[$index];
    }
}
