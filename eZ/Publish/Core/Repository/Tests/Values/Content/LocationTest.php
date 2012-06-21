<?php
/**
 * File containing the ContentTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Values\Content;
use eZ\Publish\Core\Repository\Values\Content\Location,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

/**
 *
 */
class LocationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\Repository\Values\Content\Location::getIterator
     * @covers \eZ\Publish\Core\Repository\Values\Content\Location::getProperties
     */
    public function testObjectProperties()
    {
        $object = new Location;
        $properties = array();
        foreach( $object as $property => $propValue )
        {
            //self::assertNotEquals( 'internalProperty', $property );
            $properties[] = $property;
        }
        self::assertContains( 'contentInfo', $properties, 'Property not found on Location' );
        self::assertContains( 'contentId', $properties, 'Property not found on Location' );
        self::assertContains( 'id', $properties, 'Property not found on Location' );
        self::assertContains( 'priority', $properties, 'Property not found on Location' );
        self::assertContains( 'hidden', $properties, 'Property not found on Location' );
        self::assertContains( 'invisible', $properties, 'Property not found on Location' );
        self::assertContains( 'remoteId', $properties, 'Property not found on Location' );
        self::assertContains( 'parentLocationId', $properties, 'Property not found on Location' );
        self::assertContains( 'pathString', $properties, 'Property not found on Location' );
        self::assertContains( 'path', $properties, 'Property not found on Location' );
        self::assertContains( 'modifiedSubLocationDate', $properties, 'Property not found on Location' );
        self::assertContains( 'depth', $properties, 'Property not found on Location' );
        self::assertContains( 'sortField', $properties, 'Property not found on Location' );
        self::assertContains( 'sortOrder', $properties, 'Property not found on Location' );
        self::assertContains( 'childCount', $properties, 'Property not found on Location' );
    }
}