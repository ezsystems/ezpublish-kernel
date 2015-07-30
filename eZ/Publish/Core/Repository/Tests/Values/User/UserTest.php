<?php

/**
 * File containing the UserTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\Tests\Values\User;

use eZ\Publish\Core\Repository\Values\User\User;
use PHPUnit_Framework_TestCase;

/**
 *
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\Repository\Values\User\User::getProperties
     */
    public function testObjectProperties()
    {
        $object = new User();
        $properties = $object->attributes();
        self::assertNotContains('internalFields', $properties, 'Internal property found ');
        self::assertContains('login', $properties, 'Property not found');
        self::assertContains('email', $properties, 'Property not found');
        self::assertContains('passwordHash', $properties, 'Property not found');
        self::assertContains('hashAlgorithm', $properties, 'Property not found');
        self::assertContains('enabled', $properties, 'Property not found');
        self::assertContains('maxLogin', $properties, 'Property not found');

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
