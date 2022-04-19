<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\RelationList\Type as RelationList;
use eZ\Publish\Core\FieldType\RelationList\Value;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\FieldType\Validator\TargetContentValidatorInterface;

class RelationListTest extends FieldTypeTest
{
    /** @var \Ibexa\Core\FieldType\Validator\TargetContentValidatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $targetContentValidator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->targetContentValidator = $this->createMock(TargetContentValidatorInterface::class);
    }

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
        $fieldType = new RelationList(
            $this->targetContentValidator
        );
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
        return [
            'RelationListValueValidator' => [
                'selectionLimit' => [
                    'type' => 'int',
                    'default' => 0,
                ],
            ],
        ];
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
                InvalidArgumentException::class,
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
     * Provide data sets with validator configurations which are considered
     * valid by the {@link validateValidatorConfiguration()} method.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of validator configurations.
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(),
     *      ),
     *      array(
     *          array(
     *              'IntegerValueValidator' => array(
     *                  'minIntegerValue' => 0,
     *                  'maxIntegerValue' => 23,
     *              )
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidValidatorConfiguration()
    {
        return [
            [
                [],
            ],
            [
                [
                    'RelationListValueValidator' => [
                        'selectionLimit' => 0,
                    ],
                ],
            ],
            [
                [
                    'RelationListValueValidator' => [
                        'selectionLimit' => 14,
                    ],
                ],
            ],
        ];
    }

    /**
     * Provide data sets with validator configurations which are considered
     * invalid by the {@link validateValidatorConfiguration()} method. The
     * method must return a non-empty array of validation errors when receiving
     * one of the provided values.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of validator configurations.
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(
     *              'NonExistentValidator' => array(),
     *          ),
     *      ),
     *      array(
     *          array(
     *              // Typos
     *              'InTEgervALUeVALIdator' => array(
     *                  'iinIntegerValue' => 0,
     *                  'maxIntegerValue' => 23,
     *              )
     *          )
     *      ),
     *      array(
     *          array(
     *              'IntegerValueValidator' => array(
     *                  // Incorrect value types
     *                  'minIntegerValue' => true,
     *                  'maxIntegerValue' => false,
     *              )
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInvalidValidatorConfiguration()
    {
        return [
            [
                [
                    'NonExistentValidator' => [],
                ],
            ],
            [
                [
                    'RelationListValueValidator' => [
                        'nonExistentValue' => 14,
                    ],
                ],
            ],
            [
                [
                    'RelationListValueValidator' => [
                        'selectionLimit' => 'foo',
                    ],
                ],
            ],
            [
                [
                    'RelationListValueValidator' => [
                        'selectionLimit' => -10,
                    ],
                ],
            ],
        ];
    }

    /**
     * Provides data sets with validator configuration and/or field settings and
     * field value which are considered valid by the {@link validate()} method.
     *
     * ATTENTION: This is a default implementation, which must be overwritten if
     * a FieldType supports validation!
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(
     *              "validatorConfiguration" => array(
     *                  "StringLengthValidator" => array(
     *                      "minStringLength" => 2,
     *                      "maxStringLength" => 10,
     *                  ),
     *              ),
     *          ),
     *          new TextLineValue( "lalalala" ),
     *      ),
     *      array(
     *          array(
     *              "fieldSettings" => array(
     *                  'isMultiple' => true
     *              ),
     *          ),
     *          new CountryValue(
     *              array(
     *                  "BE" => array(
     *                      "Name" => "Belgium",
     *                      "Alpha2" => "BE",
     *                      "Alpha3" => "BEL",
     *                      "IDC" => 32,
     *                  ),
     *              ),
     *          ),
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidDataForValidate()
    {
        return [
            [
                [
                    'validatorConfiguration' => [
                        'RelationListValueValidator' => [
                            'selectionLimit' => 0,
                        ],
                    ],
                ],
                new Value([5, 6, 7]),
            ],
            [
                [
                    'validatorConfiguration' => [
                        'RelationListValueValidator' => [
                            'selectionLimit' => 1,
                        ],
                    ],
                ],
                new Value([5]),
            ],
            [
                [
                    'validatorConfiguration' => [
                        'RelationListValueValidator' => [
                            'selectionLimit' => 3,
                        ],
                    ],
                ],
                new Value([5, 6]),
            ],
            [
                [
                    'validatorConfiguration' => [
                        'RelationListValueValidator' => [
                            'selectionLimit' => 3,
                        ],
                    ],
                ],
                new Value([]),
            ],
        ];
    }

    /**
     * Provides data sets with validator configuration and/or field settings,
     * field value and corresponding validation errors returned by
     * the {@link validate()} method.
     *
     * ATTENTION: This is a default implementation, which must be overwritten
     * if a FieldType supports validation!
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(
     *              "validatorConfiguration" => array(
     *                  "IntegerValueValidator" => array(
     *                      "minIntegerValue" => 5,
     *                      "maxIntegerValue" => 10
     *                  ),
     *              ),
     *          ),
     *          new IntegerValue( 3 ),
     *          array(
     *              new ValidationError(
     *                  "The value can not be lower than %size%.",
     *                  null,
     *                  array(
     *                      "%size%" => 5
     *                  ),
     *              ),
     *          ),
     *      ),
     *      array(
     *          array(
     *              "fieldSettings" => array(
     *                  "isMultiple" => false
     *              ),
     *          ),
     *          new CountryValue(
     *              "BE" => array(
     *                  "Name" => "Belgium",
     *                  "Alpha2" => "BE",
     *                  "Alpha3" => "BEL",
     *                  "IDC" => 32,
     *              ),
     *              "FR" => array(
     *                  "Name" => "France",
     *                  "Alpha2" => "FR",
     *                  "Alpha3" => "FRA",
     *                  "IDC" => 33,
     *              ),
     *          )
     *      ),
     *      array(
     *          new ValidationError(
     *              "Field definition does not allow multiple countries to be selected."
     *          ),
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInvalidDataForValidate()
    {
        return [
            [
                [
                    'validatorConfiguration' => [
                        'RelationListValueValidator' => [
                            'selectionLimit' => 3,
                        ],
                    ],
                ],
                new Value([1, 2, 3, 4]),
                [
                    new ValidationError(
                        'The selected content items number cannot be higher than %limit%.',
                        null,
                        [
                            '%limit%' => 3,
                        ],
                        'destinationContentIds'
                    ),
                ],
            ],
        ];
    }

    public function testValidateNotExistingContentRelations(): void
    {
        $destinationContentId = (int) 'invalid';
        $destinationContentId2 = (int) 'invalid-second';

        $this->targetContentValidator
            ->expects(self::exactly(2))
            ->method('validate')
            ->withConsecutive([$destinationContentId], [$destinationContentId2])
            ->willReturnOnConsecutiveCalls(
                $this->generateValidationError($destinationContentId),
                $this->generateValidationError($destinationContentId2)
            );

        $validationErrors = $this->doValidate([], new Value([$destinationContentId, $destinationContentId2]));

        self::assertIsArray($validationErrors);
        self::assertCount(2, $validationErrors);
    }

    public function testValidateInvalidContentType(): void
    {
        $destinationContentId = 12;
        $destinationContentId2 = 13;
        $allowedContentTypes = ['article', 'folder'];

        $this->targetContentValidator
            ->expects(self::exactly(2))
            ->method('validate')
            ->withConsecutive(
                [$destinationContentId, $allowedContentTypes],
                [$destinationContentId2, $allowedContentTypes]
            )
            ->willReturnOnConsecutiveCalls(
                $this->generateContentTypeValidationError('test'),
                $this->generateContentTypeValidationError('test')
            );

        $validationErrors = $this->doValidate(
            ['fieldSettings' => ['selectionContentTypes' => $allowedContentTypes]],
            new Value([$destinationContentId, $destinationContentId2])
        );

        self::assertIsArray($validationErrors);
        self::assertCount(2, $validationErrors);
    }

    private function generateValidationError(string $contentId): ValidationError
    {
        return new ValidationError(
            'Content with identifier %contentId% is not a valid relation target',
            null,
            [
                '%contentId%' => $contentId,
            ],
            'targetContentId'
        );
    }

    private function generateContentTypeValidationError(string $contentTypeIdentifier): ValidationError
    {
        return new ValidationError(
            'Content Type %contentTypeIdentifier% is not a valid relation target',
            null,
            [
                '%contentTypeIdentifier%' => $contentTypeIdentifier,
            ],
            'targetContentId'
        );
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
