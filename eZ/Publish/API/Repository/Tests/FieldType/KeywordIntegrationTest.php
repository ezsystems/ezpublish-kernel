<?php

/**
 * File contains: eZ\Publish\API\Repository\Tests\FieldType\KeywordIntegrationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\FieldType;

use eZ\Publish\Core\FieldType\Keyword\Value as KeywordValue;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Integration test for use field type.
 *
 * @group integration
 * @group field-type
 */
class KeywordIntegrationTest extends SearchMultivaluedBaseIntegrationTest
{
    /**
     * Get name of tested field type.
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezkeyword';
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
        return new KeywordValue(['foo', 'bar', 'sindelfingen']);
    }

    /**
     * Get name generated by the given field type (either via Nameable or fieldType->getName()).
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'foo, bar, sindelfingen';
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
            'eZ\\Publish\\Core\\FieldType\\Keyword\\Value',
            $field->value
        );

        $this->assertEquals(
            ['foo' => true, 'bar' => true, 'sindelfingen' => true],
            array_fill_keys($field->value->values, true)
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
                new \stdClass(),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType',
            ],
            [
                23,
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
        return new KeywordValue(['bielefeld']);
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
            'eZ\\Publish\\Core\\FieldType\\Keyword\\Value',
            $field->value
        );

        $this->assertEquals(
            ['bielefeld' => true],
            array_fill_keys($field->value->values, true)
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
            'eZ\\Publish\\Core\\FieldType\\Keyword\\Value',
            $field->value
        );

        $this->assertEquals(
            ['foo' => true, 'bar' => true, 'sindelfingen' => true],
            array_fill_keys($field->value->values, true)
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
                new KeywordValue(['bielefeld', 'sindelfingen']),
                ['bielefeld', 'sindelfingen'],
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
                ['sindelfeld', 'bielefingen'],
                new KeywordValue(['sindelfeld', 'bielefingen']),
            ],
        ];
    }

    public function providerForTestIsEmptyValue()
    {
        return [
            [new KeywordValue()],
            [new KeywordValue(null)],
            [new KeywordValue([])],
        ];
    }

    public function providerForTestIsNotEmptyValue()
    {
        return [
            [
                $this->getValidCreationFieldData(),
            ],
            [
                new KeywordValue(['0']),
            ],
        ];
    }

    /**
     * Test updating multiple contents with ezkeyword field preserves proper fields values.
     */
    public function testUpdateContentKeywords()
    {
        $contentType = $this->testCreateContentType();
        $contentService = $this->getRepository()->getContentService();

        $value01 = new KeywordValue(['foo', 'FOO', 'bar', 'baz']);
        $contentDraft = $this->createContent($value01, $contentType);
        $publishedContent01 = $contentService->publishVersion($contentDraft->versionInfo);
        $this->assertContentFieldHasCorrectData($publishedContent01->contentInfo->id, $value01);

        // create another content with the same value
        $value02 = $value01;
        $contentDraft = $this->createContent($value02, $contentType);
        $publishedContent02 = $contentService->publishVersion($contentDraft->versionInfo);
        $this->assertContentFieldHasCorrectData($publishedContent02->contentInfo->id, $value02);

        // for the first content, create draft, remove one keyword and publish new version
        $contentDraft = $contentService->createContentDraft($publishedContent01->contentInfo);
        $updateStruct = $contentService->newContentUpdateStruct();
        $value01 = new KeywordValue(['foo', 'FOO', 'bar']);
        $updateStruct->setField('data', $value01);
        $contentDraft = $contentService->updateContent($contentDraft->versionInfo, $updateStruct);
        $publishedContent01 = $contentService->publishVersion($contentDraft->versionInfo);
        $this->assertContentFieldHasCorrectData($publishedContent01->contentInfo->id, $value01);
        // reload and check the second content value01
        $this->assertContentFieldHasCorrectData($publishedContent02->contentInfo->id, $value02);

        // delete the second content
        $contentService->deleteContent($publishedContent02->contentInfo);
        // check if the first content was not affected
        $this->assertContentFieldHasCorrectData($publishedContent01->contentInfo->id, $value01);
    }

    /**
     * {@inheritdoc}
     */
    protected function createContent($fieldData, $contentType = null)
    {
        if ($contentType === null) {
            $contentType = $this->testCreateContentType();
        }

        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $createStruct->setField('name', 'Test object');
        $createStruct->setField(
            'data',
            $fieldData
        );

        $createStruct->remoteId = md5(uniqid('', true) . microtime());
        $createStruct->alwaysAvailable = true;

        return $contentService->createContent($createStruct);
    }

    /**
     * Check that the given Content Object contains proper Keywords.
     *
     * @param int $contentId
     * @param \eZ\Publish\Core\FieldType\Keyword\Value $value
     */
    private function assertContentFieldHasCorrectData($contentId, KeywordValue $value)
    {
        $contentService = $this->getRepository()->getContentService();
        $loadedContent = $contentService->loadContent($contentId, ['eng-US']);
        $dataField = $loadedContent->getField('data');
        sort($dataField->value->values);
        sort($value->values);
        $this->assertEquals($value, $dataField->value);
    }

    public function testKeywordsAreCaseSensitive()
    {
        $contentType = $this->testCreateContentType();
        $publishedContent01 = $this->createAndPublishContent('Foo', $contentType, md5(uniqid(__METHOD__, true)));
        $publishedContent02 = $this->createAndPublishContent('foo', $contentType, md5(uniqid(__METHOD__, true)));

        $data = $publishedContent01->getField('data')->value;
        $this->assertCount(1, $data->values);
        $this->assertEquals('Foo', $data->values[0]);

        $data = $publishedContent02->getField('data')->value;
        $this->assertCount(1, $data->values);
        $this->assertEquals('foo', $data->values[0]);
    }

    /**
     * Create and publish content of $contentType with $fieldData.
     *
     * @param mixed $fieldData
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param string $remoteId
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createAndPublishContent($fieldData, ContentType $contentType, $remoteId)
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $createStruct->setField('name', 'Test object');
        $createStruct->setField(
            'data',
            $fieldData
        );

        $createStruct->remoteId = $remoteId;
        $createStruct->alwaysAvailable = true;

        $contentDraft = $contentService->createContent($createStruct);

        return $contentService->publishVersion($contentDraft->versionInfo);
    }

    protected function getValidSearchValueOne()
    {
        return 'add';
    }

    protected function getValidSearchValueTwo()
    {
        return 'branch';
    }

    protected function getValidMultivaluedSearchValuesOne()
    {
        return ['add', 'branch'];
    }

    protected function getValidMultivaluedSearchValuesTwo()
    {
        return ['commit', 'delete'];
    }

    public function checkFullTextSupport()
    {
        // Does nothing
    }

    protected function getFullTextIndexedFieldData()
    {
        return [
            ['add', 'branch'],
        ];
    }

    public function providerForTestTruncateField()
    {
        return [
            [new KeywordValue()],
            [new KeywordValue(null)],
            [new KeywordValue([])],
            // an empty array is what actually REST API sets when field should be truncated
            [[]],
        ];
    }

    /**
     * Test that setting an empty value truncates field data.
     *
     * @dataProvider providerForTestTruncateField
     * @param mixed $emptyValue data representing an empty value
     * @todo Move this method to BaseIntegrationTest when fixed for all field types.
     */
    public function testTruncateField($emptyValue)
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $fieldType = $repository->getFieldTypeService()->getFieldType($this->getTypeName());

        $contentDraft = $this->testCreateContent();
        $publishedContent = $contentService->publishVersion($contentDraft->versionInfo);

        $contentDraft = $contentService->createContentDraft($publishedContent->contentInfo);
        $updateStruct = $contentService->newContentUpdateStruct();
        $updateStruct->setField('data', $emptyValue);
        $contentDraft = $contentService->updateContent($contentDraft->versionInfo, $updateStruct);
        $publishedContent = $contentService->publishVersion($contentDraft->versionInfo);

        $content = $contentService->loadContent($publishedContent->contentInfo->id, ['eng-US']);

        $fieldValue = $content->getField('data')->value;
        self::assertTrue(
            $fieldType->isEmptyValue($fieldValue),
            'Field value is not empty: ' . var_export($fieldValue, true)
        );
    }
}
