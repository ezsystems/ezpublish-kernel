<?php
/**
 * File containing the PagePartsConverter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
