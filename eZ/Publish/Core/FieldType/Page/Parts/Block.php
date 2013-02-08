<?php
/**
 * File containing the Service class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page\Parts;

class Block extends Base
{
    public function addItem( Item $item )
    {
        $this->properties['items'][] = $item;
    }
}
