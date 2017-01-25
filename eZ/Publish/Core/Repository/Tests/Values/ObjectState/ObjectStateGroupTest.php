<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Values\ObjectState;

use eZ\Publish\API\Repository\Tests\Values\ValueObjectTestTrait;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectStateGroup;
use PHPUnit_Framework_TestCase;

/**
 * Test internal integrity of @see \eZ\Publish\Core\Repository\Values\ObjectState\ObjectStateGroup ValueObject.
 */
class ObjectStateGroupTest extends PHPUnit_Framework_TestCase
{
    use ValueObjectTestTrait;

    /**
     * Test a new class and default values on properties.
     *
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__construct
     */
    public function testNewClass()
    {
        $objectStateGroup = new ObjectStateGroup();

        $this->assertPropertiesCorrect(
            [
                'id' => null,
                'identifier' => null,
                'defaultLanguageCode' => null,
                'languageCodes' => null,
                'names' => [],
                'descriptions' => [],
            ],
            $objectStateGroup
        );
    }

    /**
     * Test retrieving missing property.
     *
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__get
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testMissingProperty()
    {
        $objectStateGroup = new ObjectStateGroup();
        $value = $objectStateGroup->notDefined;
        $this->fail('Succeeded getting non existing property');
    }

    /**
     * Test setting read only property.
     *
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__set
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException
     */
    public function testReadOnlyProperty()
    {
        $objectStateGroup = new ObjectStateGroup();
        $objectStateGroup->id = 42;
        $this->fail('Succeeded setting read only property');
    }

    /**
     * Test if property exists.
     *
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__isset
     */
    public function testIsPropertySet()
    {
        $objectStateGroup = new ObjectStateGroup();
        $value = isset($objectStateGroup->notDefined);
        $this->assertEquals(false, $value);

        $value = isset($objectStateGroup->id);
        $this->assertEquals(true, $value);
    }

    /**
     * Test unsetting a property.
     *
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__unset
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException
     */
    public function testUnsetProperty()
    {
        $objectStateGroup = new ObjectStateGroup(['id' => 2]);
        unset($objectStateGroup->id);
        $this->fail('Unsetting read-only property succeeded');
    }
}
