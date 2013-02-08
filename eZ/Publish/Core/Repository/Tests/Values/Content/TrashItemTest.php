<?php
/**
 * File containing the TrashItemTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Values\Content;

use eZ\Publish\Core\Repository\Values\Content\TrashItem;
use PHPUnit_Framework_TestCase;

/**
 *
 */
class TrashItemTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\Repository\Values\Content\TrashItem::getProperties
     */
    public function testObjectProperties()
    {
        $object = new TrashItem;
        $properties = $object->attributes();
        //self::assertNotContains( 'internalFields', $properties, 'Internal property found ' );
        self::assertContains( 'contentInfo', $properties, 'Property not found' );
        self::assertContains( 'contentId', $properties, 'Property not found' );
        self::assertContains( 'id', $properties, 'Property not found' );
        self::assertContains( 'priority', $properties, 'Property not found' );
        self::assertContains( 'hidden', $properties, 'Property not found' );
        self::assertContains( 'invisible', $properties, 'Property not found' );
        self::assertContains( 'remoteId', $properties, 'Property not found' );
        self::assertContains( 'parentLocationId', $properties, 'Property not found' );
        self::assertContains( 'pathString', $properties, 'Property not found' );
        self::assertContains( 'path', $properties, 'Property not found' );
        self::assertContains( 'depth', $properties, 'Property not found' );
        self::assertContains( 'sortField', $properties, 'Property not found' );
        self::assertContains( 'sortOrder', $properties, 'Property not found' );

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
