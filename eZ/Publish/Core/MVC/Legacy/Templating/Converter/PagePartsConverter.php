<?php
/**
 * File containing the PagePartsConverter class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Converter;

use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\FieldType\Page\Parts\Zone;
use eZ\Publish\Core\MVC\Legacy\Templating\Adapter\BlockAdapter;
use eZ\Publish\Core\MVC\Legacy\Templating\Adapter\ZoneAdapter;
use InvalidArgumentException;

class PagePartsConverter implements ObjectConverter
{
    public function convert( $object )
    {
        if ( !is_object( $object ) )
            throw new InvalidArgumentException( 'Transferred object must be a Page\\Parts\\Block object. Got ' . gettype( $object ) );

        if ( $object instanceof Block )
        {
            return new BlockAdapter( $object );
        }
        else if ( $object instanceof Zone )
        {
            return new ZoneAdapter( $object );
        }

        throw new InvalidArgumentException( 'Transferred object must be a Page\\Parts\\Block object. Got ' . get_class( $object ) );
    }
}
