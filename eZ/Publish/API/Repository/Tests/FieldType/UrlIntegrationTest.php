<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\FieldType;

use eZ\Publish\Core\FieldType\Url\Value as UrlValue;
use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Integration test for use field type.
 *
 * @group integration
 * @group field-type
 */
class UrlIntegrationTest extends SearchBaseIntegrationTest
{
    /**
     * Get name of tested field type.
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezurl';
    }

    /**
     * Get expected settings schema.
     *
     * @return array
     */
    public function getSettingsSchema()
    {
        return [];
    }

    /**
     * Get a valid $fieldSettings value.
     *
     * @return mixed
     */
    public function getValidFieldSettings()
    {
        return [];
    }

    /**
     * Get $fieldSettings value not accepted by the field type.
     *
     * @return mixed
     */
    public function getInvalidFieldSettings()
    {
        return [
            'somethingUnknown' => 0,
        ];
    }

    /**
     * Get expected validator schema.
     *
     * @return array
     */
    public function getValidatorSchema()
    {
        return [];
    }

    /**
     * Get a valid $validatorConfiguration.
     *
     * @return mixed
     */
    public function getValidValidatorConfiguration()
    {
        return [];
    }

    /**
     * Get $validatorConfiguration not accepted by the field type.
     *
     * @return mixed
     */
    public function getInvalidValidatorConfiguration()
    {
        return [
            'unknown' => ['value' => 23],
        ];
    }

    /**
     * Get initial field data for valid object creation.
     *
     * @return mixed
     */
    public function getValidCreationFieldData()
    {
        return new UrlValue('http://example.com', 'Example');
    }

    /**
     * Get name generated by the given field type (via fieldType->getName()).
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Example';
    }

    /**
     * Asserts that the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()}
     * was stored and loaded correctly.
     *
     * @param Field $field
     */
    public function assertFieldDataLoadedCorrect(Field $field)
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\Url\\Value',
            $field->value
        );

        $expectedData = [
            'link' => 'http://example.com',
            'text' => 'Example',
        ];
        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );
    }

    /**
     * Get field data which will result in errors during creation.
     *
     * This is a PHPUnit data provider.
     *
     * The returned records must contain of an error producing data value and
     * the expected exception class (from the API or SPI, not implementation
     * specific!) as the second element. For example:
     *
     * <code>
     * array(
     *      array(
     *          new DoomedValue( true ),
     *          'eZ\\Publish\\API\\Repository\\Exceptions\\ContentValidationException'
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array[]
     */
    public function provideInvalidCreationFieldData()
    {
        return [
            [
                new UrlValue(23),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType',
            ],
            [
                new UrlValue('http://example.com', 23),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType',
            ],
        ];
    }

    /**
     * Get update field externals data.
     *
     * @return array
     */
    public function getValidUpdateFieldData()
    {
        return new UrlValue('http://example.com/2', 'Example  2');
    }

    /**
     * Get externals updated field data values.
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function assertUpdatedFieldDataLoadedCorrect(Field $field)
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\Url\\Value',
            $field->value
        );

        $expectedData = [
            'link' => 'http://example.com/2',
            'text' => 'Example  2',
        ];
        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );
    }

    /**
     * Get field data which will result in errors during update.
     *
     * This is a PHPUnit data provider.
     *
     * The returned records must contain of an error producing data value and
     * the expected exception class (from the API or SPI, not implementation
     * specific!) as the second element. For example:
     *
     * <code>
     * array(
     *      array(
     *          new DoomedValue( true ),
     *          'eZ\\Publish\\API\\Repository\\Exceptions\\ContentValidationException'
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array[]
     */
    public function provideInvalidUpdateFieldData()
    {
        return $this->provideInvalidCreationFieldData();
    }

    /**
     * Asserts the the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()}
     * was copied and loaded correctly.
     *
     * @param Field $field
     */
    public function assertCopiedFieldDataLoadedCorrectly(Field $field)
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\Url\\Value',
            $field->value
        );

        $expectedData = [
            'link' => 'http://example.com',
            'text' => 'Example',
        ];
        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );
    }

    /**
     * Get data to test to hash method.
     *
     * This is a PHPUnit data provider
     *
     * The returned records must have the the original value assigned to the
     * first index and the expected hash result to the second. For example:
     *
     * <code>
     * array(
     *      array(
     *          new MyValue( true ),
     *          array( 'myValue' => true ),
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array
     */
    public function provideToHashData()
    {
        return [
            [
                new UrlValue('http://example.com'),
                [
                    'link' => 'http://example.com',
                    'text' => null,
                ],
            ],
            [
                new UrlValue('http://example.com', 'Link text'),
                [
                    'link' => 'http://example.com',
                    'text' => 'Link text',
                ],
            ],
        ];
    }

    /**
     * Get expectations for the fromHash call on our field value.
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function provideFromHashData()
    {
        return [
            [
                ['link' => 'http://example.com/sindelfingen'],
                new UrlValue('http://example.com/sindelfingen'),
            ],
            [
                ['link' => 'http://example.com/sindelfingen', 'text' => 'Foo'],
                new UrlValue('http://example.com/sindelfingen', 'Foo'),
            ],
        ];
    }

    public function providerForTestIsEmptyValue()
    {
        return [
            [new UrlValue()],
            [new UrlValue(null)],
            [new UrlValue('')],
            [new UrlValue('', '')],
        ];
    }

    public function providerForTestIsNotEmptyValue()
    {
        return [
            [
                $this->getValidCreationFieldData(),
            ],
            [
                new UrlValue('http://example.com'),
            ],
        ];
    }

    protected function getValidSearchValueOne()
    {
        return new UrlValue('http://ample.com', 'Ample');
    }

    protected function getValidSearchValueTwo()
    {
        return new UrlValue('http://example.com', 'Example');
    }

    protected function getSearchTargetValueOne()
    {
        return 'http://ample.com';
    }

    protected function getSearchTargetValueTwo()
    {
        return 'http://example.com';
    }

    protected function getAdditionallyIndexedFieldData()
    {
        return [
            [
                'value_text',
                // ensure case-insensitivity
                'AMPLE',
                'EXAMPLE',
            ],
        ];
    }

    protected function getFullTextIndexedFieldData()
    {
        return [
            ['ample', 'example'],
        ];
    }
}
