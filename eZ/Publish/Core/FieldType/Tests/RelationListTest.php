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
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\SPI\Persistence\Content\Handler as SPIContentHandler;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

class RelationListTest extends FieldTypeTest
{
    private const DESTINATION_CONTENT_ID_14 = 14;
    private const DESTINATION_CONTENT_ID_22 = 22;

    /** @var \eZ\Publish\SPI\Persistence\Content\Handler */
    private $contentHandler;

    protected function setUp()
    {
        parent::setUp();

        $versionInfo14 = new VersionInfo([
            'versionNo' => 1,
            'names' => [
                'en_GB' => 'name_14_en_GB',
                'de_DE' => 'Name_14_de_DE',
            ],
        ]);
        $versionInfo22 = new VersionInfo([
            'versionNo' => 1,
            'names' => [
                'en_GB' => 'name_22_en_GB',
                'de_DE' => 'Name_22_de_DE',
            ],
        ]);
        $currentVersionNoFor14 = 44;
        $destinationContentInfo14 = $this->createMock(ContentInfo::class);
        $destinationContentInfo14
            ->method('__get')
            ->willReturnMap([
                ['currentVersionNo', $currentVersionNoFor14],
                ['mainLanguageCode', 'en_GB'],
            ]);
        $currentVersionNoFor22 = 22;
        $destinationContentInfo22 = $this->createMock(ContentInfo::class);
        $destinationContentInfo22
            ->method('__get')
            ->willReturnMap([
                ['currentVersionNo', $currentVersionNoFor22],
                ['mainLanguageCode', 'en_GB'],
            ]);

        $this->contentHandler = $this->createMock(SPIContentHandler::class);
        $this->contentHandler
            ->method('loadContentInfo')
            ->willReturnMap([
                [self::DESTINATION_CONTENT_ID_14, $destinationContentInfo14],
                [self::DESTINATION_CONTENT_ID_22, $destinationContentInfo22],
            ]);

        $this->contentHandler
            ->method('loadVersionInfo')
            ->willReturnMap([
                [self::DESTINATION_CONTENT_ID_14, $currentVersionNoFor14, $versionInfo14],
                [self::DESTINATION_CONTENT_ID_22, $currentVersionNoFor22, $versionInfo22],
            ]);
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
        $fieldType = new RelationList($this->contentHandler);
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
        return array(
            'RelationListValueValidator' => array(
                'selectionLimit' => array(
                    'type' => 'int',
                    'default' => 0,
                ),
            ),
        );
    }

    /**
     * Returns the settings schema expected from the field type.
     *
     * @return array
     */
    protected function getSettingsSchemaExpectation()
    {
        return array(
            'selectionMethod' => array(
                'type' => 'int',
                'default' => RelationList::SELECTION_BROWSE,
            ),
            'selectionDefaultLocation' => array(
                'type' => 'string',
                'default' => null,
            ),
            'selectionContentTypes' => array(
                'type' => 'array',
                'default' => array(),
            ),
        );
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
        return array(
            array(
                true,
                InvalidArgumentException::class,
            ),
        );
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
        return array(
            array(
                new Value(),
                new Value(),
            ),
            array(
                23,
                new Value(array(23)),
            ),
            array(
                new ContentInfo(array('id' => 23)),
                new Value(array(23)),
            ),
            array(
                array(23, 42),
                new Value(array(23, 42)),
            ),
        );
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
        return array(
            array(
                new Value(array(23, 42)),
                array('destinationContentIds' => array(23, 42)),
            ),
            array(
                new Value(),
                array('destinationContentIds' => array()),
            ),
        );
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
        return array(
            array(
                array('destinationContentIds' => array(23, 42)),
                new Value(array(23, 42)),
            ),
            array(
                array('destinationContentIds' => array()),
                new Value(),
            ),
        );
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
        return array(
            array(
                array(
                    'selectionMethod' => RelationList::SELECTION_BROWSE,
                    'selectionDefaultLocation' => 23,
                ),
            ),
            array(
                array(
                    'selectionMethod' => RelationList::SELECTION_DROPDOWN,
                    'selectionDefaultLocation' => 'foo',
                ),
            ),
            array(
                array(
                    'selectionMethod' => RelationList::SELECTION_DROPDOWN,
                    'selectionDefaultLocation' => 'foo',
                    'selectionContentTypes' => array(1, 2, 3),
                ),
            ),
            array(
                array(
                    'selectionMethod' => RelationList::SELECTION_LIST_WITH_RADIO_BUTTONS,
                    'selectionDefaultLocation' => 'foo',
                    'selectionContentTypes' => array(1, 2, 3),
                ),
            ),
            array(
                array(
                    'selectionMethod' => RelationList::SELECTION_LIST_WITH_CHECKBOXES,
                    'selectionDefaultLocation' => 'foo',
                    'selectionContentTypes' => array(1, 2, 3),
                ),
            ),
            array(
                array(
                    'selectionMethod' => RelationList::SELECTION_MULTIPLE_SELECTION_LIST,
                    'selectionDefaultLocation' => 'foo',
                    'selectionContentTypes' => array(1, 2, 3),
                ),
            ),
            array(
                array(
                    'selectionMethod' => RelationList::SELECTION_TEMPLATE_BASED_MULTIPLE,
                    'selectionDefaultLocation' => 'foo',
                    'selectionContentTypes' => array(1, 2, 3),
                ),
            ),
            array(
                array(
                    'selectionMethod' => RelationList::SELECTION_TEMPLATE_BASED_SINGLE,
                    'selectionDefaultLocation' => 'foo',
                    'selectionContentTypes' => array(1, 2, 3),
                ),
            ),
        );
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
        return array(
            array(
                // Invalid value for 'selectionMethod'
                array(
                    'selectionMethod' => true,
                    'selectionDefaultLocation' => 23,
                ),
            ),
            array(
                // Invalid value for 'selectionDefaultLocation'
                array(
                    'selectionMethod' => RelationList::SELECTION_DROPDOWN,
                    'selectionDefaultLocation' => array(),
                ),
            ),
            array(
                // Invalid value for 'selectionContentTypes'
                array(
                    'selectionMethod' => RelationList::SELECTION_DROPDOWN,
                    'selectionDefaultLocation' => 23,
                    'selectionContentTypes' => true,
                ),
            ),
            array(
                // Invalid value for 'selectionMethod'
                array(
                    'selectionMethod' => 9,
                    'selectionDefaultLocation' => 23,
                    'selectionContentTypes' => true,
                ),
            ),
        );
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
        return array(
            array(
                array(),
            ),
            array(
                array(
                    'RelationListValueValidator' => array(
                        'selectionLimit' => 0,
                    ),
                ),
            ),
            array(
                array(
                    'RelationListValueValidator' => array(
                        'selectionLimit' => 14,
                    ),
                ),
            ),
        );
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
        return array(
            array(
                array(
                    'NonExistentValidator' => array(),
                ),
            ),
            array(
                array(
                    'RelationListValueValidator' => array(
                        'nonExistentValue' => 14,
                    ),
                ),
            ),
            array(
                array(
                    'RelationListValueValidator' => array(
                        'selectionLimit' => 'foo',
                    ),
                ),
            ),
            array(
                array(
                    'RelationListValueValidator' => array(
                        'selectionLimit' => -10,
                    ),
                ),
            ),
        );
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
        return array(
            array(
                array(
                    'validatorConfiguration' => array(
                        'RelationListValueValidator' => array(
                            'selectionLimit' => 0,
                        ),
                    ),
                ),
                new Value([5, 6, 7]),
            ),
            array(
                array(
                    'validatorConfiguration' => array(
                        'RelationListValueValidator' => array(
                            'selectionLimit' => 1,
                        ),
                    ),
                ),
                new Value([5]),
            ),
            array(
                array(
                    'validatorConfiguration' => array(
                        'RelationListValueValidator' => array(
                            'selectionLimit' => 3,
                        ),
                    ),
                ),
                new Value([5, 6]),
            ),
            array(
                array(
                    'validatorConfiguration' => array(
                        'RelationListValueValidator' => array(
                            'selectionLimit' => 3,
                        ),
                    ),
                ),
                new Value([]),
            ),
        );
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
        return array(
            array(
                array(
                    'validatorConfiguration' => array(
                        'RelationListValueValidator' => array(
                            'selectionLimit' => 3,
                        ),
                    ),
                ),
                new Value([1, 2, 3, 4]),
                array(
                    new ValidationError(
                        'The selected content items number cannot be higher than %limit%.',
                        null,
                        array(
                            '%limit%' => 3,
                        ),
                        'destinationContentIds'
                    ),
                ),
            ),
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::getRelations
     */
    public function testGetRelations()
    {
        $ft = $this->createFieldTypeUnderTest();
        $this->assertEquals(
            array(
                Relation::FIELD => array(70, 72),
            ),
            $ft->getRelations($ft->acceptValue(array(70, 72)))
        );
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezobjectrelationlist';
    }

    /**
     * @dataProvider provideDataForGetName
     */
    public function testGetName(SPIValue $value, array $fieldSettings = [], string $languageCode = 'en_GB', $expected)
    {
        $fieldDefinitionMock = $this->getFieldDefinitionMock($fieldSettings);

        $name = $this->getFieldTypeUnderTest()->getName($value, $fieldDefinitionMock, $languageCode);

        self::assertSame($expected, $name);
    }

    public function provideDataForGetName(): array
    {
        return [
            [$this->getEmptyValueExpectation(), [], 'en_GB', ''],
            [new Value([self::DESTINATION_CONTENT_ID_14, self::DESTINATION_CONTENT_ID_22]), [], 'en_GB', 'name_14_en_GB name_22_en_GB'],
            [new Value([self::DESTINATION_CONTENT_ID_14, self::DESTINATION_CONTENT_ID_22]), [], 'de_DE', 'Name_14_de_DE Name_22_de_DE'],
        ];
    }
}
