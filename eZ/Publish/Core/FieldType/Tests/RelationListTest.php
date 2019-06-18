<?php

/**
 * File containing the RelationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\RelationList\Type as RelationList;
use eZ\Publish\Core\FieldType\RelationList\Value;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class RelationListTest extends FieldTypeTest
{
    /**
     * Returns the field type under test.
     *
     * This method is used by all test cases to retrieve the field type under
     * test. Just create the FieldType instance using mocks from the provided
     * get*Mock() methods and/or custom get*Mock() implementations. You MUST
     * NOT take care for test case wide caching of the field type, just return
     * a new instance from this method!
     *
     * @return \eZ\Publish\Core\FieldType\Relation\Type
     */
    protected function createFieldTypeUnderTest()
    {
        $fieldType = new RelationList();
        $fieldType->setTransformationProcessor($this->getTransformationProcessorMock());

        return $fieldType;
    }

    /**
     * Returns the validator configuration schema expected from the field type.
     *
     * @return array
     */
    protected function getValidatorConfigurationSchemaExpectation()
    {
        return [];
    }

    /**
     * Returns the settings schema expected from the field type.
     *
     * @return array
     */
    protected function getSettingsSchemaExpectation()
    {
        return [
            'selectionMethod' => [
                'type' => 'int',
                'default' => RelationList::SELECTION_BROWSE,
            ],
            'selectionDefaultLocation' => [
                'type' => 'string',
                'default' => null,
            ],
            'selectionContentTypes' => [
                'type' => 'array',
                'default' => [],
            ],
        ];
    }

    /**
     * Returns the empty value expected from the field type.
     *
     * @return Value
     */
    protected function getEmptyValueExpectation()
    {
        // @todo FIXME: Is this correct?
        return new Value();
    }

    /**
     * Data provider for invalid input to acceptValue().
     *
     * Returns an array of data provider sets with 2 arguments: 1. The invalid
     * input to acceptValue(), 2. The expected exception type as a string. For
     * example:
     *
     * <code>
     *  return array(
     *      array(
     *          new \stdClass(),
     *          'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
     *      ),
     *      array(
     *          array(),
     *          'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInvalidInputForAcceptValue()
    {
        return [
            [
                true,
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ],
        ];
    }

    /**
     * Data provider for valid input to acceptValue().
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to acceptValue(), 2. The expected return value from acceptValue().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          __FILE__,
     *          new BinaryFileValue( array(
     *              'path' => __FILE__,
     *              'fileName' => basename( __FILE__ ),
     *              'fileSize' => filesize( __FILE__ ),
     *              'downloadCount' => 0,
     *              'mimeType' => 'text/plain',
     *          ) )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidInputForAcceptValue()
    {
        return [
            [
                new Value(),
                new Value(),
            ],
            [
                23,
                new Value([23]),
            ],
            [
                new ContentInfo(['id' => 23]),
                new Value([23]),
            ],
            [
                [23, 42],
                new Value([23, 42]),
            ],
        ];
    }

    /**
     * Provide input for the toHash() method.
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to toHash(), 2. The expected return value from toHash().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          new BinaryFileValue( array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ) ),
     *          array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInputForToHash()
    {
        return [
            [
                new Value([23, 42]),
                ['destinationContentIds' => [23, 42]],
            ],
            [
                new Value(),
                ['destinationContentIds' => []],
            ],
        ];
    }

    /**
     * Provide input to fromHash() method.
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to fromHash(), 2. The expected return value from fromHash().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ),
     *          new BinaryFileValue( array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ) )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInputForFromHash()
    {
        return [
            [
                ['destinationContentIds' => [23, 42]],
                new Value([23, 42]),
            ],
            [
                ['destinationContentIds' => []],
                new Value(),
            ],
        ];
    }

    /**
     * Provide data sets with field settings which are considered valid by the
     * {@link validateFieldSettings()} method.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(),
     *      ),
     *      array(
     *          array( 'rows' => 2 )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidFieldSettings()
    {
        return [
            [
                [
                    'selectionMethod' => RelationList::SELECTION_BROWSE,
                    'selectionDefaultLocation' => 23,
                ],
            ],
            [
                [
                    'selectionMethod' => RelationList::SELECTION_DROPDOWN,
                    'selectionDefaultLocation' => 'foo',
                ],
            ],
            [
                [
                    'selectionMethod' => RelationList::SELECTION_DROPDOWN,
                    'selectionDefaultLocation' => 'foo',
                    'selectionContentTypes' => [1, 2, 3],
                ],
            ],
            [
                [
                    'selectionMethod' => RelationList::SELECTION_LIST_WITH_RADIO_BUTTONS,
                    'selectionDefaultLocation' => 'foo',
                    'selectionContentTypes' => [1, 2, 3],
                ],
            ],
            [
                [
                    'selectionMethod' => RelationList::SELECTION_LIST_WITH_CHECKBOXES,
                    'selectionDefaultLocation' => 'foo',
                    'selectionContentTypes' => [1, 2, 3],
                ],
            ],
            [
                [
                    'selectionMethod' => RelationList::SELECTION_MULTIPLE_SELECTION_LIST,
                    'selectionDefaultLocation' => 'foo',
                    'selectionContentTypes' => [1, 2, 3],
                ],
            ],
            [
                [
                    'selectionMethod' => RelationList::SELECTION_TEMPLATE_BASED_MULTIPLE,
                    'selectionDefaultLocation' => 'foo',
                    'selectionContentTypes' => [1, 2, 3],
                ],
            ],
            [
                [
                    'selectionMethod' => RelationList::SELECTION_TEMPLATE_BASED_SINGLE,
                    'selectionDefaultLocation' => 'foo',
                    'selectionContentTypes' => [1, 2, 3],
                ],
            ],
        ];
    }

    /**
     * Provide data sets with field settings which are considered invalid by the
     * {@link validateFieldSettings()} method. The method must return a
     * non-empty array of validation error when receiving such field settings.
     *
     * ATTENTION: This is a default implementation, which must be overwritten
     * if a FieldType supports field settings!
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          true,
     *      ),
     *      array(
     *          array( 'nonExistentKey' => 2 )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInValidFieldSettings()
    {
        return [
            [
                // Invalid value for 'selectionMethod'
                [
                    'selectionMethod' => true,
                    'selectionDefaultLocation' => 23,
                ],
            ],
            [
                // Invalid value for 'selectionDefaultLocation'
                [
                    'selectionMethod' => RelationList::SELECTION_DROPDOWN,
                    'selectionDefaultLocation' => [],
                ],
            ],
            [
                // Invalid value for 'selectionContentTypes'
                [
                    'selectionMethod' => RelationList::SELECTION_DROPDOWN,
                    'selectionDefaultLocation' => 23,
                    'selectionContentTypes' => true,
                ],
            ],
            [
                // Invalid value for 'selectionMethod'
                [
                    'selectionMethod' => 9,
                    'selectionDefaultLocation' => 23,
                    'selectionContentTypes' => true,
                ],
            ],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::getRelations
     */
    public function testGetRelations()
    {
        $ft = $this->createFieldTypeUnderTest();
        $this->assertEquals(
            [
                Relation::FIELD => [70, 72],
            ],
            $ft->getRelations($ft->acceptValue([70, 72]))
        );
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezobjectrelationlist';
    }

    /**
     * @dataProvider provideDataForGetName
     * @expectedException \RuntimeException
     */
    public function testGetName(SPIValue $value, $expected)
    {
        $this->getFieldTypeUnderTest()->getName($value);
    }

    public function provideDataForGetName()
    {
        return [
            [$this->getEmptyValueExpectation(), ''],
        ];
    }
}
