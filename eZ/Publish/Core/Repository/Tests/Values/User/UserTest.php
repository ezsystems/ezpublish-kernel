<?php

/**
 * File containing the UserTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Values\User;

use eZ\Publish\API\Repository\Tests\Values\ValueObjectTestTrait;
use eZ\Publish\Core\Repository\Values\User\User;
use PHPUnit_Framework_TestCase;

/**
 * Test internal integrity of @see \eZ\Publish\Core\Repository\Values\User\User ValueObject.
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    use ValueObjectTestTrait;

    /**
     * Test a new class and default values on properties.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\User::__construct
     */
    public function testNewClass()
    {
        $user = new User();

        $this->assertPropertiesCorrect(
            [
                'login' => null,
                'email' => null,
                'passwordHash' => null,
                'hashAlgorithm' => null,
                'maxLogin' => null,
                'enabled' => false,
            ],
            $user
        );
    }

    /**
     * Test retrieving missing property.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\User::__get
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testMissingProperty()
    {
        $user = new User();
        $value = $user->notDefined;
        self::fail('Succeeded getting non existing property');
    }

    /**
     * Test setting read only property.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\User::__set
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException
     */
    public function testReadOnlyProperty()
    {
        $user = new User();
        $user->login = 'user';
        self::fail('Succeeded setting read only property');
    }

    /**
     * Test if property exists.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\User::__isset
     */
    public function testIsPropertySet()
    {
        $user = new User();
        $value = isset($user->notDefined);
        self::assertEquals(false, $value);

        $value = isset($user->login);
        self::assertEquals(true, $value);
    }

    /**
     * Test unsetting a property.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\User::__unset
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException
     */
    public function testUnsetProperty()
    {
        $user = new User(['login' => 'admin']);
        unset($user->login);
        self::fail('Unsetting read-only property succeeded');
    }
}
