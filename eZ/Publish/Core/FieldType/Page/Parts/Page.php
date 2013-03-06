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

/**
 * @property-read string $layout The layout identifier (e.g. "2ZonesLayout1").
 * @property-read \eZ\Publish\Core\FieldType\Page\Parts\Zone[] $zones Zone objects for current page, indexed by their Id.
 */
class Page extends Base
{
    /**
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Zone[]
     */
    protected $zones = array();

    /**
     * @var array
     */
    private $zoneKeys;

    /**
     * @var string
     */
    protected $layout;

    /**
     * Returns zone by numeric index.
     *
     * @param int $index
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Zone
     *
     * @throws \OutOfBoundsException If $index is invalid.
     */
    public function getZoneByIndex( $index )
    {
        if ( !isset( $this->zoneKeys ) )
            $this->zoneKeys = array_keys( $this->zones );

        if ( !isset( $this->zoneKeys[$index] ) )
            throw new OutOfBoundsException( "Could not find zone with index #$index" );

        return $this->zones[$this->zoneKeys[$index]];
    }
}
