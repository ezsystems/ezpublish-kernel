<?php
/**
 * File contains: eZ\Publish\API\Repository\Tests\FieldType\BinaryFileIntegrationTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;

use eZ\Publish\Core\FieldType\BinaryFile\Value as BinaryFileValue;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Integration test for use field type
 *
 * @group integration
 * @group field-type
 */
class BinaryFileIntegrationTest extends FileSearchBaseIntegrationTest
{
    /**
     * Stores the loaded image path for copy test.
     */
    protected static $loadedBinaryFilePath;

    /**
     * IOService storage prefix for the tested Type's files
     * @var string
     */
    protected static $storagePrefixConfigKey = 'binaryfile_storage_prefix';

    protected function getStoragePrefix()
    {
        return $this->getConfigValue( self::$storagePrefixConfigKey );
    }

    /**
     * Sets up fixture data.
     *
     * @return array
     */
    protected function getFixtureData()
    {
        return array(
            'create' => array(
                'id' => null,
                'inputUri' => ( $path = __DIR__ . '/_fixtures/image.jpg' ),
                'fileName' => 'Icy-Night-Flower-Binary.jpg',
                'fileSize' => filesize( $path ),
                'mimeType' => 'image/jpeg',
                // Left out'downloadCount' by intention (will be set to 0)
            ),
            'update' => array(
                'id' => null,
                'inputUri' => ( $path = __DIR__ . '/_fixtures/image.png' ),
                'fileName' => 'Blue-Blue-Blue-Sindelfingen.png',
                'fileSize' => filesize( $path ),
                'downloadCount' => 23,
                // Left out 'mimeType' by intention (will be auto-detected)
            ),
        );
    }

    /**
     * Get name of tested field type
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezbinaryfile';
    }

    /**
     * Get expected settings schema
     *
     * @return array
     */
    public function getSettingsSchema()
    {
        return array();
    }

    /**
     * Get a valid $fieldSettings value
     *
     * @return mixed
     */
    public function getValidFieldSettings()
    {
        return array();
    }

    /**
     * Get $fieldSettings value not accepted by the field type
     *
     * @return mixed
     */
    public function getInvalidFieldSettings()
    {
        return array(
            'somethingUnknown' => 0,
        );
    }

    /**
     * Get expected validator schema
     *
     * @return array
     */
    public function getValidatorSchema()
    {
        return array(
            'FileSizeValidator' => array(
                'maxFileSize' => array(
                    'type'    => 'int',
                    'default' => false,
                ),
            )
        );
    }

    /**
     * Get a valid $validatorConfiguration
     *
     * @return mixed
     */
    public function getValidValidatorConfiguration()
    {
        return array(
            'FileSizeValidator' => array(
                'maxFileSize' => 2 * 1024 * 1024, // 2 MB
            ),
        );
    }

    /**
     * Get $validatorConfiguration not accepted by the field type
     *
     * @return mixed
     */
    public function getInvalidValidatorConfiguration()
    {
        return array(
            'StringLengthValidator' => array(
                'minStringLength' => new \stdClass(),
            )
        );
    }

    /**
     * Get initial field data for valid object creation
     *
     * @return mixed
     */
    public function getValidCreationFieldData()
    {
        $fixtureData = $this->getFixtureData();
        return new BinaryFileValue( $fixtureData['create'] );
    }

    /**
     * Asserts that the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()}
     * was stored and loaded correctly.
     *
     * @param Field $field
     *
     * @return void
     */
    public function assertFieldDataLoadedCorrect( Field $field )
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\BinaryFile\\Value',
            $field->value
        );

        $fixtureData = $this->getFixtureData();
        $expectedData = $fixtureData['create'];

        // Will change during storage
        unset( $expectedData['id'] );
        $expectedData['inputUri'] = null;

        $this->assertNotEmpty( $field->value->id );
        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );

        $this->assertTrue(
            $this->uriExistsOnIO( $field->value->uri ),
            "File {$field->value->uri} doesn't exist"
        );

        self::$loadedBinaryFilePath = $field->value->id;
    }

    /**
     * Get field data which will result in errors during creation
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
        return array(
            array(
                array(
                    'id' => '/foo/bar/sindelfingen.pdf',
                ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentValue',
            ),
            array(
                new BinaryFileValue(
                    array(
                        'id' => '/foo/bar/sindelfingen.pdf',
                    )
                ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentValue',
            ),
        );
    }

    /**
     * Get update field externals data
     *
     * @return array
     */
    public function getValidUpdateFieldData()
    {
        $fixtureData = $this->getFixtureData();
        return new BinaryFileValue( $fixtureData['update'] );
    }

    /**
     * Get externals updated field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function assertUpdatedFieldDataLoadedCorrect( Field $field )
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\BinaryFile\\Value',
            $field->value
        );

        $fixtureData = $this->getFixtureData();
        $expectedData = $fixtureData['update'];

        // Will change during storage
        unset( $expectedData['id'] );
        $expectedData['inputUri'] = null;

        $this->assertNotEmpty( $field->value->id );
        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );

        $this->assertTrue(
            $this->uriExistsOnIO( $field->value->uri ),
            "File {$field->value->uri} doesn't exist."
        );
    }

    /**
     * Get field data which will result in errors during update
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
    public function assertCopiedFieldDataLoadedCorrectly( Field $field )
    {
        $this->assertFieldDataLoadedCorrect( $field );

        $this->assertEquals(
            self::$loadedBinaryFilePath,
            $field->value->id
        );
    }

    /**
     * Get data to test to hash method
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
        $fixture = $this->getFixtureData();
        $fixture['create']['downloadCount'] = 0;
        $fixture['create']['uri'] = $fixture['create']['inputUri'];
        $fixture['create']['path'] = $fixture['create']['inputUri'];

        $fieldValue = $this->getValidCreationFieldData();
        $fieldValue->uri = $fixture['create']['uri'];

        return array(
            array(
                $fieldValue,
                $fixture['create'],
            ),
        );
    }

    /**
     * Get expectations for the fromHash call on our field value
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function provideFromHashData()
    {
        $fixture = $this->getFixtureData();
        $fixture['create']['downloadCount'] = 0;
        $fixture['create']['uri'] = $fixture['create']['inputUri'];

        $fieldValue = $this->getValidCreationFieldData();
        $fieldValue->uri = $fixture['create']['uri'];

        return array(
            array(
                $fixture['create'],
                $fieldValue
            ),
        );
    }

    public function providerForTestIsEmptyValue()
    {
        return array(
            array( new BinaryFileValue ),
            array( new BinaryFileValue( array() ) ),
        );
    }

    public function providerForTestIsNotEmptyValue()
    {
        return array(
            array(
                $this->getValidCreationFieldData()
            ),
        );
    }

    protected function getValidSearchValueOne()
    {
        return new BinaryFileValue(
            array(
                'inputUri' => ( $path = __DIR__ . '/_fixtures/image.jpg' ),
                'fileName' => 'blue-blue-blue-sindelfingen.jpg',
                'fileSize' => filesize( $path ),
            )
        );
    }

    protected function getValidSearchValueTwo()
    {
        return new BinaryFileValue(
            array(
                'inputUri' => ( $path = __DIR__ . '/_fixtures/image.png' ),
                'fileName' => 'icy-night-flower-binary.png',
                'fileSize' => filesize( $path ),
            )
        );
    }

    protected function getSearchTargetValueOne()
    {
        $value = $this->getValidSearchValueOne();
        return $value->fileName;
    }

    protected function getSearchTargetValueTwo()
    {
        $value = $this->getValidSearchValueTwo();
        return $value->fileName;
    }

    /**
     * Redefined here in order to execute before tests with modified fields below,
     * which depend on it for the returned value.
     */
    public function testCreateTestContent()
    {
        if ( ltrim( get_class( $this->getSetupFactory() ), '\\' ) === 'eZ\\Publish\\API\\Repository\\Tests\\SetupFactory\\Legacy' )
        {
            $this->markTestSkipped(
                "BinaryFile field type is not searchable with Field criterion and sort clause in Legacy search engine"
            );
        }

        return parent::testCreateTestContent();
    }

    public function criteriaProviderModifiedFieldMimeType()
    {
        return $this->provideCriteria( "image/jpeg", "image/png" );
    }

    /**
     * Tests Content Search filtering with Field criterion on the alternative text modified field
     *
     * @dataProvider criteriaProviderModifiedFieldMimeType
     * @depends testCreateTestContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param boolean $includesOne
     * @param boolean $includesTwo
     * @param array $context
     */
    public function testFilterContentModifiedFieldMimeType(
        Criterion $criterion,
        $includesOne,
        $includesTwo,
        array $context
    )
    {
        $this->assertFilterContentModifiedField(
            $criterion,
            $includesOne,
            $includesTwo,
            $context,
            true,
            "mime_type"
        );
    }

    /**
     * Tests Content Search querying with Field criterion on the alternative text modified field
     *
     * @dataProvider criteriaProviderModifiedFieldMimeType
     * @depends testCreateTestContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param boolean $includesOne
     * @param boolean $includesTwo
     * @param array $context
     */
    public function testQueryContentModifiedFieldMimeType(
        Criterion $criterion,
        $includesOne,
        $includesTwo,
        array $context
    )
    {
        $this->assertFilterContentModifiedField(
            $criterion,
            $includesOne,
            $includesTwo,
            $context,
            false,
            "mime_type"
        );
    }

    public function criteriaProviderModifiedFieldFileSize()
    {
        $valueOne = $this->getValidSearchValueOne();
        $valueTwo = $this->getValidSearchValueTwo();

        return $this->provideCriteria( $valueOne->fileSize, $valueTwo->fileSize );
    }

    /**
     * Tests Content Search filtering with Field criterion on the file size modified field
     *
     * @dataProvider criteriaProviderModifiedFieldFileSize
     * @depends testCreateTestContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param boolean $includesOne
     * @param boolean $includesTwo
     * @param array $context
     */
    public function testFilterContentModifiedFieldFileSize(
        Criterion $criterion,
        $includesOne,
        $includesTwo,
        array $context
    )
    {
        $this->assertFilterContentModifiedField(
            $criterion,
            $includesOne,
            $includesTwo,
            $context,
            true,
            "file_size"
        );
    }

    /**
     * Tests Content Search querying with Field criterion on the file size modified field
     *
     * @dataProvider criteriaProviderModifiedFieldFileSize
     * @depends testCreateTestContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param boolean $includesOne
     * @param boolean $includesTwo
     * @param array $context
     */
    public function testQueryContentModifiedFieldFileSize(
        Criterion $criterion,
        $includesOne,
        $includesTwo,
        array $context
    )
    {
        $this->assertFilterContentModifiedField(
            $criterion,
            $includesOne,
            $includesTwo,
            $context,
            false,
            "file_size"
        );
    }

    /**
     * Tests Content Search sort with Field sort clause on the alternative text modified field
     *
     * @dataProvider sortClauseProvider
     * @depends testCreateTestContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause
     * @param boolean $ascending
     * @param array $context
     */
    public function testSortContentModifiedFieldMimeType(
        SortClause $sortClause,
        $ascending,
        array $context
    )
    {
        $this->assertSortContentModifiedField(
            $sortClause,
            $ascending,
            $context,
            "mime_type"
        );
    }

    /**
     * Tests Content Search sort with Field sort clause on the file size modified field
     *
     * @dataProvider sortClauseProvider
     * @depends testCreateTestContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause
     * @param boolean $ascending
     * @param array $context
     */
    public function testSortContentModifiedFieldFieldSize(
        SortClause $sortClause,
        $ascending,
        array $context
    )
    {
        $this->assertSortContentModifiedField(
            $sortClause,
            $ascending,
            $context,
            "file_size"
        );
    }
}
