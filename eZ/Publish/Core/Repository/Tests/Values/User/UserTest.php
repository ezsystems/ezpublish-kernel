<?php
/**
 * File containing the UserTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Values\User;
use eZ\Publish\Core\Repository\Values\User\User,
    PHPUnit_Framework_TestCase;

/**
 *
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\Repository\Values\User\User::getIterator
     * @covers \eZ\Publish\Core\Repository\Values\User\User::getProperties
     */
    public function testObjectProperties()
    {
        $object = new User;
        $properties = $object->attributes();
        self::assertNotContains( 'internalFields', $properties, 'Internal property found ' );
        self::assertContains( 'login', $properties, 'Property not found' );
        self::assertContains( 'email', $properties, 'Property not found' );
        self::assertContains( 'passwordHash', $properties, 'Property not found' );
        self::assertContains( 'hashAlgorithm', $properties, 'Property not found' );
        self::assertContains( 'enabled', $properties, 'Property not found' );
        self::assertContains( 'maxLogin', $properties, 'Property not found' );

        // check for duplicates and double check existence of property
        $propertiesHash = array();
        foreach ( $properties as $property )
        {
            if ( isset( $propertiesHash[$property] ) )
            {
                self::fail( "Property '{$property}' exists several times in properties list" );
            }
            else if ( !isset( $object->$property ) )
            {
                self::fail( "Property '{$property}' does not exist on object, even though it was hinted to be there" );
            }
            $propertiesHash[$property] = 1;
        }
    }
}
