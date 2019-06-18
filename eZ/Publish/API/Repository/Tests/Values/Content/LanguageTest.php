<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Tests\Values\ValueObjectTestTrait;
use PHPUnit\Framework\TestCase;

class LanguageTest extends TestCase
{
    use ValueObjectTestTrait;

    /**
     * Test default properties of just created class.
     */
    public function testNewClass()
    {
        $language = new Language();

        $this->assertPropertiesCorrect(
            [
                'id' => null,
                'languageCode' => null,
                'name' => null,
                'enabled' => null,
            ],
            $language
        );
    }

    /**
     * Test retrieving missing property.
     *
     * @covers \eZ\Publish\API\Repository\Values\Content\Language::__get
     */
    public function testMissingProperty()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException::class);
        $this->expectExceptionMessage('Property \'notDefined\' not found on class');

        $language = new Language();
        $value = $language->notDefined;
        self::fail('Succeeded getting non existing property');
    }

    /**
     * Test setting read only property.
     *
     * @covers \eZ\Publish\API\Repository\Values\Content\Language::__set
     */
    public function testReadOnlyProperty()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException::class);
        $this->expectExceptionMessage('Property \'id\' is readonly on class');

        $language = new Language();
        $language->id = 42;
        self::fail('Succeeded setting read only property');
    }

    /**
     * Test if property exists.
     *
     * @covers \eZ\Publish\API\Repository\Values\Content\Language::__isset
     */
    public function testIsPropertySet()
    {
        $language = new Language();
        $value = isset($language->notDefined);
        self::assertEquals(false, $value);

        $value = isset($language->id);
        self::assertEquals(true, $value);
    }

    /**
     * Test unsetting a property.
     *
     * @covers \eZ\Publish\API\Repository\Values\Content\Language::__unset
     */
    public function testUnsetProperty()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException::class);
        $this->expectExceptionMessage('Property \'id\' is readonly on class');

        $language = new Language(['id' => 2]);
        unset($language->id);
        self::fail('Unsetting read-only property succeeded');
    }
}
