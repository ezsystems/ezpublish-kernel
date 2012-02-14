<?php
/**
 * File containing WritableArrayObject tool class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Tools;

use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\PropertyReadOnlyExceptionStub;

class ImmutableArrayObject extends \ArrayObject
{
    public function append( $value )
    {
        throw new PropertyReadOnlyExceptionStub( '' );
    }

    public function exchangeArray( $input )
    {
        throw new PropertyReadOnlyExceptionStub( 'containing array' );
    }

    public function offsetSet( $index, $newval )
    {
        throw new PropertyReadOnlyExceptionStub( $index );
    }

    public function offsetUnset( $index )
    {
        throw new PropertyReadOnlyExceptionStub( $index );
    }
}
