<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Values\User;

use eZ\Publish\API\Repository\Tests\Values\ValueObjectTestTrait;
use eZ\Publish\Core\Repository\Values\User\Policy;
use PHPUnit\Framework\TestCase;

class PolicyTest extends TestCase
{
    use ValueObjectTestTrait;

    /**
     * Test a new class and default values on properties.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\Policy::__construct
     */
    public function testNewClass()
    {
        $this->assertPropertiesCorrect(
            [
                'id' => null,
                'roleId' => null,
                'module' => null,
                'function' => null,
                'limitations' => [],
            ],
            new Policy()
        );
    }

    /**
     * Test retrieving missing property.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\Policy::__get
     */
    public function testMissingProperty()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException::class);

        $policy = new Policy();
        $value = $policy->notDefined;
        self::fail('Succeeded getting non existing property');
    }

    /**
     * Test setting read only property.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\Policy::__set
     */
    public function testReadOnlyProperty()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException::class);

        $policy = new Policy();
        $policy->id = 42;
        self::fail('Succeeded setting read only property');
    }

    /**
     * Test if property exists.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\Policy::__isset
     */
    public function testIsPropertySet()
    {
        $policy = new Policy();
        $value = isset($policy->notDefined);
        self::assertFalse($value);

        $value = isset($policy->id);
        self::assertTrue($value);
    }

    /**
     * Test unsetting a property.
     *
     * @covers \eZ\Publish\API\Repository\Values\User\Policy::__unset
     */
    public function testUnsetProperty()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException::class);

        $policy = new Policy(['id' => 1]);
        unset($policy->id);
        self::fail('Unsetting read-only property succeeded');
    }
}
