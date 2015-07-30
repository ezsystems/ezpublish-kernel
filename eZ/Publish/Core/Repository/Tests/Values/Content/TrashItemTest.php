<?php

/**
 * File containing the TrashItemTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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
        $object = new TrashItem();
        $properties = $object->attributes();
        //self::assertNotContains( 'internalFields', $properties, 'Internal property found ' );
        self::assertContains('contentInfo', $properties, 'Property not found');
        self::assertContains('contentId', $properties, 'Property not found');
        self::assertContains('id', $properties, 'Property not found');
        self::assertContains('priority', $properties, 'Property not found');
        self::assertContains('hidden', $properties, 'Property not found');
        self::assertContains('invisible', $properties, 'Property not found');
        self::assertContains('remoteId', $properties, 'Property not found');
        self::assertContains('parentLocationId', $properties, 'Property not found');
        self::assertContains('pathString', $properties, 'Property not found');
        self::assertContains('path', $properties, 'Property not found');
        self::assertContains('depth', $properties, 'Property not found');
        self::assertContains('sortField', $properties, 'Property not found');
        self::assertContains('sortOrder', $properties, 'Property not found');

        // check for duplicates and double check existence of property
        $propertiesHash = array();
        foreach ($properties as $property) {
            if (isset($propertiesHash[$property])) {
                self::fail("Property '{$property}' exists several times in properties list");
            } elseif (!isset($object->$property)) {
                self::fail("Property '{$property}' does not exist on object, even though it was hinted to be there");
            }
            $propertiesHash[$property] = 1;
        }
    }
}
