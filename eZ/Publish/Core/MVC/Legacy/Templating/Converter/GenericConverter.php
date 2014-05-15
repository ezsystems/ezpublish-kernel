<?php
/**
 * File containing the GenericConverter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Converter;

use eZ\Publish\Core\MVC\Legacy\Templating\LegacyAdapter;

/**
 * Generic converter.
 */
class GenericConverter implements ObjectConverter
{
    /**
     * Converts $object to make it compatible with eZTemplate API.
     *
     * @param mixed $object
     *
     * @throws \InvalidArgumentException If $object is actually not an object
     *
     * @return mixed|\eZ\Publish\Core\MVC\Legacy\Templating\LegacyCompatible
     */
    public function convert( $object )
    {
        if ( !is_object( $object ) )
            throw new \InvalidArgumentException( 'Transferred object must be a real object. Got ' . gettype( $object ) );

        return new LegacyAdapter( $object );
    }
}
