<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\Content;

use eZ\Publish\API\Repository\Tests\Values\ValueObjectTestTrait;
use eZ\Publish\API\Repository\Values\Content\Section;
use PHPUnit\Framework\TestCase;

class SectionTest extends TestCase
{
    use ValueObjectTestTrait;

    /**
     * Test a new class and default values on properties.
     *
     * @covers \eZ\Publish\API\Repository\Values\Content\Section::__construct
     */
    public function testNewClass()
    {
        $section = new Section();

        $this->assertPropertiesCorrect(
            [
                'id' => null,
                'identifier' => null,
                'name' => null,
            ],
            $section
        );
    }

    /**
     * Test retrieving missing property.
     *
     * @covers \eZ\Publish\API\Repository\Values\Content\Section::__get
     */
    public function testMissingProperty()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException::class);

        $section = new Section();
        $value = $section->notDefined;
        self::fail('Succeeded getting non existing property');
    }

    /**
     * Test setting read only property.
     *
     * @covers \eZ\Publish\API\Repository\Values\Content\Section::__set
     */
    public function testReadOnlyProperty()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException::class);

        $section = new Section();
        $section->id = 22;
        self::fail('Succeeded setting read only property');
    }

    /**
     * Test if property exists.
     *
     * @covers \eZ\Publish\API\Repository\Values\Content\Section::__isset
     */
    public function testIsPropertySet()
    {
        $section = new Section();
        $value = isset($section->notDefined);
        self::assertFalse($value);

        $value = isset($section->id);
        self::assertTrue($value);
    }

    /**
     * Test unsetting a property.
     *
     * @covers \eZ\Publish\API\Repository\Values\Content\Section::__unset
     */
    public function testUnsetProperty()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException::class);

        $section = new Section(['id' => 1]);
        unset($section->id);
        self::fail('Unsetting read-only property succeeded');
    }
}
