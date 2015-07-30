<?php

/**
 * File containing the UserGroupTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\Tests\Values\User;

use eZ\Publish\Core\Repository\Values\User\UserGroup;
use PHPUnit_Framework_TestCase;

/**
 *
 */
class UserGroupTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\Repository\Values\User\User::getProperties
     */
    public function testObjectProperties()
    {
        $object = new UserGroup();
        $properties = $object->attributes();
        self::assertNotContains('internalFields', $properties, 'Internal property found ');
        self::assertContains('parentId', $properties, 'Property not found');
        self::assertContains('subGroupCount', $properties, 'Property not found');

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
