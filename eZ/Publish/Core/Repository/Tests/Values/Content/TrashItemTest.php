<?php
/**
 * File containing the ContentTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Values\Content;
use eZ\Publish\Core\Repository\Values\Content\TrashItem,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

/**
 *
 */
class TrashItemTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\Repository\Values\Content\TrashItem::getIterator
     * @covers \eZ\Publish\Core\Repository\Values\Content\TrashItem::getProperties
     */
    public function testObjectProperties()
    {
        $object = new TrashItem;
        $properties = array();
        foreach( $object as $property => $propValue )
        {
            //self::assertNotEquals( 'internalProperty', $property );
            $properties[] = $property;
        }
        self::assertContains( 'contentInfo', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'contentId', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'id', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'priority', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'hidden', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'invisible', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'remoteId', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'parentLocationId', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'pathString', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'path', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'modifiedSubLocationDate', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'depth', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'sortField', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'sortOrder', $properties, 'Property not found on TrashItem' );
        self::assertContains( 'childCount', $properties, 'Property not found on TrashItem' );
    }
}