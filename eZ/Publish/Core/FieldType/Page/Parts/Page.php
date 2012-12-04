<?php
/**
 * File containing the Service class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page\Parts;

class Page extends Base
{
    /**
     * Adds new $zone to the Page object
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Zone $zone
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Zone
     */
    public function addZone( Zone $zone )
    {
        $this->properties['zones'][] = $zone;
        return $zone;
    }

    /**
     * Returns zone object by given $index
     *
     * @param int $index
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Zone
     */
    public function getZone( $index )
    {
        $zone = null;

        if( isset( $this->properties['zones'][$index] ) )
            $zone = $this->properties['zones'][$index];

        return $zone;
    }
}
