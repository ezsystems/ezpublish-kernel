<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Values\ObjectState;

use eZ\Publish\API\Repository\Tests\Values\ValueObjectTestTrait;
use eZ\Publish\Core\Repository\Tests\Values\MultiLanguageTestTrait;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectState;
use PHPUnit\Framework\TestCase;

/**
 * Test internal integrity of @see \eZ\Publish\Core\Repository\Values\ObjectState\ObjectState ValueObject.
 */
class ObjectStateTest extends TestCase
{
    use ValueObjectTestTrait;
    use MultiLanguageTestTrait;

    /**
     * Test a new class and default values on properties.
     *
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectState::__construct
     */
    public function testNewClass()
    {
        $objectState = new ObjectState();

        $this->assertPropertiesCorrect(
            [
                'id' => null,
                'identifier' => null,
                'priority' => null,
                'mainLanguageCode' => null,
                'languageCodes' => null,
                'names' => [],
                'descriptions' => [],
            ],
            $objectState
        );
    }

    /**
     * Test a new class with unified multi language logic properties.
     *
     * @return \eZ\Publish\Core\Repository\Values\ObjectState\ObjectState
     */
    public function testNewClassWithMultiLanguageProperties()
    {
        $properties = [
            'names' => [
                'eng-US' => 'Name',
                'pol-PL' => 'Nazwa',
            ],
            'descriptions' => [
                'eng-US' => 'Description',
                'pol-PL' => 'Opis',
            ],
            'mainLanguageCode' => 'eng-US',
            'prioritizedLanguages' => ['pol-PL', 'eng-US'],
        ];

        $objectState = new ObjectState($properties);
        $this->assertPropertiesCorrect($properties, $objectState);

        // BC test:
        self::assertTrue(isset($objectState->defaultLanguageCode));
        self::assertSame('eng-US', $objectState->defaultLanguageCode);

        return $objectState;
    }

    /**
     * Test retrieving missing property.
     *
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectState::__get
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__get
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testMissingProperty()
    {
        $objectState = new ObjectState();
        $value = $objectState->notDefined;
        $this->fail('Succeeded getting non existing property');
    }

    /**
     * Test setting read only property.
     *
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectState::__set
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__set
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException
     */
    public function testReadOnlyProperty()
    {
        $objectState = new ObjectState();
        $objectState->id = 42;
        $this->fail('Succeeded setting read only property');
    }

    /**
     * Test if property exists.
     *
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectState::__isset
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__isset
     */
    public function testIsPropertySet()
    {
        $objectState = new ObjectState();
        $value = isset($objectState->notDefined);
        $this->assertFalse($value);

        $value = isset($objectState->id);
        $this->assertTrue($value);
    }

    /**
     * Test unsetting a property.
     *
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectState::__unset
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__unset
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException
     */
    public function testUnsetProperty()
    {
        $objectState = new ObjectState(['id' => 2]);
        unset($objectState->id);
        $this->fail('Unsetting read-only property succeeded');
    }
}
