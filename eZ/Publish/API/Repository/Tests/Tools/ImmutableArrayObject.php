<?php
/**
 * File containing WritableArrayObject tool class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Tools;

class ReadonlyArrayObject extends \ArrayObject
{
    public function append( $value )
    {
        // TODO: Correct exception if available
        throw new \RuntimeException( "Setting index '' not allowed." );
    }

    public function exchangeArray( $input )
    {
        // TODO: Correct exception if available
        throw new \RuntimeException( "Exchanging array not allowed." );
    }

    public function offsetSet( $index, $newval )
    {
        // TODO: Correct exception if available
        throw new \RuntimeException( "Setting index '$index' not allowed." );
    }

    public function offsetUnset( $index )
    {
        // TODO: Correct exception if available
        throw new \RuntimeException( "Setting index '$index' not allowed." );
    }
}
