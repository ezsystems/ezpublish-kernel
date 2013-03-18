<?php
/**
 * File contains: eZ\Publish\API\Repository\Tests\FieldType\BaseIntegrationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;

use eZ\Publish\API\Repository\Tests;
use eZ\Publish\API\Repository;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * Integration test for legacy storage field types
 *
 * This abstract base test case is supposed to be the base for field type
 * integration tests. It basically calls all involved methods in the field type
 * ``Converter`` and ``Storage`` implementations. Fo get it working implement
 * the abstract methods in a sensible way.
 *
 * The following actions are performed by this test using the custom field
 * type:
 *
 * - Create a new content type with the given field type
 * - Load created content type
 * - Create content object of new content type
 * - Load created content
 * - Publish created content
 * - Update content
 * - Copy created content
 * - Remove copied content
 * - Test toHash
 * - Test fromHash
 *
 * @group integration
 * @group field-type
 *
 * @todo Finalize dependencies to other tests (including groups!)
 */
abstract class BaseIntegrationTest extends Tests\BaseTest
{
    /**
     * Identifier of the custom field
     *
     * @var string
     */
    protected $customFieldIdentifier = "data";

    /**
     * Get name of tested field type
     *
     * @return string
     */
    abstract public function getTypeName();

    /**
     * Get expected settings schema
     *
     * @return array
     */
    abstract public function getSettingsSchema();

    /**
     * Get a valid $fieldSettings value
     *
     * @return mixed
     */
    abstract public function getValidFieldSettings();

    /**
     * Get $fieldSettings value not accepted by the field type
     *
     * @return mixed
     */
    abstract public function getInvalidFieldSettings();

    /**
     * Get expected validator schema
     *
     * @return array
     */
    abstract public function getValidatorSchema();

    /**
     * Get a valid $validatorConfiguration
     *
     * @return mixed
     */
    abstract public function getValidValidatorConfiguration();

    /**
     * Get $validatorConfiguration not accepted by the field type
     *
     * @return mixed
     */
    abstract public function getInvalidValidatorConfiguration();

    /**
     * Get initial field data for valid object creation
     *
     * @return mixed
     */
    abstract public function getValidCreationFieldData();

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
    abstract public function assertFieldDataLoadedCorrect( Field $field );

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
    abstract public function provideInvalidCreationFieldData();

    /**
     * Get valid field data for updating content
     *
     * @return mixed
     */
    abstract public function getValidUpdateFieldData();

    /**
     * Asserts the the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidUpdateFieldData()}
     * was stored and loaded correctly.
     *
     * @param Field $field
     */
    abstract public function assertUpdatedFieldDataLoadedCorrect( Field $field );

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
    abstract public function provideInvalidUpdateFieldData();

    /**
     * Asserts the the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()}
     * was copied and loaded correctly.
     *
     * @param Field $field
     */
    abstract public function assertCopiedFieldDataLoadedCorrectly( Field $field );

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
    abstract public function provideToHashData();

    /**
     * Get hashes and their respective converted values
     *
     * This is a PHPUnit data provider
     *
     * The returned records must have the the input hash assigned to the
     * first index and the expected value result to the second. For example:
     *
     * <code>
     * array(
     *      array(
     *          array( 'myValue' => true ),
     *          new MyValue( true ),
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array
     */
    abstract public function provideFromHashData();

    /**
     * Marks FieldType integration tests skipped against memory stub
     *
     * Since the FieldType integration tests rely on multiple factors which are
     * hard to mimic by the memory stub, these can only be run against a real
     * core implementation with a real persistence back end.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        if ( $this->getRepository() instanceof \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub )
        {
            $this->markTestSkipped(
                'FieldType integration tests cannot be run against memory stub.'
            );
        }
    }

    /**
     * Method called after content creation
     *
     * Useful, if additional stuff should be executed (like creating the actual
     * user).
     *
     * We cannot just overwrite the testCreateContent method, since this messes
     * up PHPUnits @depends sorting of tests, so everything will be skipped.
     *
     * @param Repository\Repository $repository
     * @param Repository\Values\Content\Content $content
     *
     * @return void
     */
    public function postCreationHook( Repository\Repository $repository, Repository\Values\Content\Content $content )
    {
        // Do nothing by default
    }

    /**
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     */
    public function testCreateContentType()
    {
        $contentType = $this->createContentType(
            $this->getValidFieldSettings(),
            $this->getValidValidatorConfiguration()
        );

        $this->assertNotNull( $contentType->id );

        return $contentType;
    }

    /**
     * Creates a content type under test with $fieldSettings and
     * $validatorConfiguration.
     *
     * $typeCreateOverride and $fieldCreateOverride can be used to selectively
     * override settings on the type create struct and field create struct.
     *
     * @param mixed $fieldSettings
     * @param mixed $validatorConfiguration
     * @param array $typeCreateOverride
     * @param array $fieldCreateOverride
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createContentType( $fieldSettings, $validatorConfiguration, array $typeCreateOverride = array(), array $fieldCreateOverride = array() )
    {
        $repository         = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $createStruct = $contentTypeService->newContentTypeCreateStruct(
            'test-' . $this->getTypeName()
        );
        $createStruct->mainLanguageCode = $this->getOverride( 'mainLanguageCode', $typeCreateOverride, 'eng-GB' );
        $createStruct->remoteId     = $this->getTypeName();
        $createStruct->names        = $this->getOverride( 'names', $typeCreateOverride, array( 'eng-GB' => 'Test' ) );
        $createStruct->creatorId    = 14;
        $createStruct->creationDate = $this->createDateTime();

        $nameFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'name', 'ezstring'
        );
        $nameFieldCreate->names      = array( 'eng-GB' => 'Title' );
        $nameFieldCreate->fieldGroup = 'main';
        $nameFieldCreate->position   = 1;
        $nameFieldCreate->isTranslatable = true;
        $createStruct->addFieldDefinition( $nameFieldCreate );

        $dataFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'data', $this->getTypeName()
        );
        $dataFieldCreate->names      = $this->getOverride( 'names', $fieldCreateOverride, array( 'eng-GB' => 'Title' ) );
        $dataFieldCreate->fieldGroup = 'main';
        $dataFieldCreate->position   = 2;
        $dataFieldCreate->isTranslatable = $this->getOverride( 'isTranslatable', $fieldCreateOverride, false );

        // Custom settings
        $dataFieldCreate->fieldSettings = $fieldSettings;
        $dataFieldCreate->validatorConfiguration = $validatorConfiguration;

        $createStruct->addFieldDefinition( $dataFieldCreate );

        $contentGroup     = $contentTypeService->loadContentTypeGroupByIdentifier( 'Content' );
        $contentTypeDraft = $contentTypeService->createContentType( $createStruct, array( $contentGroup ) );

        $contentTypeService->publishContentTypeDraft( $contentTypeDraft );
        $contentType = $contentTypeService->loadContentType( $contentTypeDraft->id );

        return $contentType;
    }

    /**
     * Retrieves a value for $key from $overrideValues, falling back to
     * $default
     *
     * @param string $key
     * @param array $overrideValues
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getOverride( $key, array $overrideValues, $default )
    {
        return ( isset( $overrideValues[$key] ) ? $overrideValues[$key] : $default );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::isEmptyValue
     * @dataProvider providerForTestIsEmptyValue
     */
    public function testIsEmptyValue( $value )
    {
        $this->assertTrue( $this->getRepository()->getFieldTypeService()->buildFieldType( $this->getTypeName() )->isEmptyValue( $value ) );
    }

    abstract public function providerForTestIsEmptyValue();

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::isEmptyValue
     * @dataProvider providerForTestIsNotEmptyValue
     */
    public function testIsNotEmptyValue( $value )
    {
        $this->assertFalse( $this->getRepository()->getFieldTypeService()->buildFieldType( $this->getTypeName() )->isEmptyValue( $value ) );
    }

    abstract public function providerForTestIsNotEmptyValue();

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::isEmptyValue
     */
    public function testIsEmptyValueWithNull()
    {
        $this->assertTrue( $this->getRepository()->getFieldTypeService()->buildFieldType( $this->getTypeName() )->isEmptyValue( null ) );
    }

    /**
     * @depends testCreateContentType
     */
    public function testContentTypeField( $contentType )
    {
        $this->assertSame(
            $this->getTypeName(),
            $contentType->fieldDefinitions[1]->fieldTypeIdentifier
        );
    }

    /**
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentType
     * @depends testCreateContentType
     */
    public function testLoadContentTypeField()
    {
        $contentType = $this->testCreateContentType();

        $repository         = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        return $contentTypeService->loadContentType( $contentType->id );
    }

    /**
     * @depends testLoadContentTypeField
     */
    public function testLoadContentTypeFieldType( $contentType )
    {
        $this->assertSame(
            $this->getTypeName(),
            $contentType->fieldDefinitions[1]->fieldTypeIdentifier
        );

        return $contentType->fieldDefinitions[1];
    }

    public function testSettingsSchema()
    {
        $repository       = $this->getRepository();
        $fieldTypeService = $repository->getFieldTypeService();
        $fieldType = $fieldTypeService->getFieldType( $this->getTypeName() );

        $this->assertEquals(
            $this->getSettingsSchema(),
            $fieldType->getSettingsSchema()
        );
    }

    /**
     * @depends testLoadContentTypeFieldType
     */
    public function testLoadContentTypeFieldData( FieldDefinition $fieldDefinition )
    {
        $this->assertEquals(
            $this->getTypeName(),
            $fieldDefinition->fieldTypeIdentifier,
            'Loaded fieldTypeIdentifier does not match.'
        );
        $this->assertEquals(
            $this->getValidFieldSettings(),
            $fieldDefinition->fieldSettings,
            'Loaded fieldSettings do not match.'
        );
        $this->assertEquals(
            $this->getValidValidatorConfiguration(),
            $fieldDefinition->validatorConfiguration,
            'Loaded validatorConfiguration does not match.'
        );
    }

    /**
     * @depends testCreateContentType
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     */
    public function testCreateContentTypeFailsWithInvalidFieldSettings()
    {
        $this->createContentType(
            $this->getInvalidFieldSettings(),
            $this->getValidValidatorConfiguration()
        );
    }

    public function testValidatorSchema()
    {
        $repository       = $this->getRepository();
        $fieldTypeService = $repository->getFieldTypeService();
        $fieldType = $fieldTypeService->getFieldType( $this->getTypeName() );

        $this->assertEquals(
            $this->getValidatorSchema(),
            $fieldType->getValidatorConfigurationSchema()
        );
    }

    /**
     * @depends testCreateContentType
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     */
    public function testCreateContentTypeFailsWithInvalidValidatorConfiguration()
    {
        $this->createContentType(
            $this->getValidFieldSettings(),
            $this->getInvalidValidatorConfiguration()
        );
    }

    /**
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent;
     * @depends testLoadContentTypeField
     */
    public function testCreateContent()
    {
        return $this->createContent( $this->getValidCreationFieldData() );
    }

    /**
     * Creates content with $fieldData
     *
     * @param mixed $fieldData
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createContent( $fieldData, $contentType = null )
    {
        if ( $contentType === null )
        {
            $contentType = $this->testCreateContentType();
        }

        $repository     = $this->getRepository();
        $contentService = $repository->getContentService();

        $createStruct = $contentService->newContentCreateStruct( $contentType, 'eng-US' );
        $createStruct->setField( 'name', 'Test object' );
        $createStruct->setField(
            'data',
            $fieldData
        );

        $createStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $createStruct->alwaysAvailable = true;

        return $contentService->createContent( $createStruct );
    }

    /**
     * @depends testCreateContent
     */
    public function testCreatedFieldType( $content )
    {
        foreach ( $content->getFields() as $field )
        {
            if ( $field->fieldDefIdentifier === $this->customFieldIdentifier )
            {
                return $field;
            }
        }

        $this->fail( "Custom field not found." );
    }

    /**
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     * @depends testCreateContent
     */
    public function testPublishContent()
    {
        $draft = $this->testCreateContent();

        if ( $draft->getVersionInfo()->status !== Repository\Values\Content\VersionInfo::STATUS_DRAFT )
        {
            $this->markTestSkipped( "Provided content object is not a draft." );
        }

        $repository     = $this->getRepository();
        $contentService = $repository->getContentService();

        return $contentService->publishVersion( $draft->getVersionInfo() );
    }

    /**
     * @depends testPublishContent
     */
    public function testPublishedFieldType( $content )
    {
        foreach ( $content->getFields() as $field )
        {
            if ( $field->fieldDefIdentifier === $this->customFieldIdentifier )
            {
                return $field;
            }
        }

        $this->fail( "Custom field not found." );
    }

    /**
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     * @dep_ends testCreateContent
     */
    public function testLoadField()
    {
        $content = $this->testCreateContent();

        $repository     = $this->getRepository();
        $contentService = $repository->getContentService();
        return $contentService->loadContent( $content->contentInfo->id );
    }

    /**
     * @depends testLoadField
     */
    public function testLoadFieldType( $content )
    {
        foreach ( $content->getFields() as $field )
        {
            if ( $field->fieldDefIdentifier === $this->customFieldIdentifier )
            {
                return $field;
            }
        }

        $this->fail( "Custom field not found." );
    }

    /**
     * @depends testLoadFieldType
     */
    public function testLoadExternalData( Field $field )
    {
        $this->assertFieldDataLoadedCorrect( $field );
    }

    /**
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     * @depends testLoadFieldType
     */
    public function testUpdateField()
    {
        return $this->updateContent( $this->getValidUpdateFieldData() );
    }

    /**
     * Updates the standard published content object with $fieldData
     *
     * @param mixed $fieldData
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function updateContent( $fieldData )
    {
        $content = $this->testPublishContent();

        $repository     = $this->getRepository();
        $contentService = $repository->getContentService();

        $draft = $contentService->createContentDraft( $content->contentInfo );

        $updateStruct = $contentService->newContentUpdateStruct();
        $updateStruct->setField(
            $this->customFieldIdentifier,
            $fieldData
        );

        return $contentService->updateContent( $draft->versionInfo, $updateStruct );
    }

    /**
     * @depends testUpdateField
     */
    public function testUpdateTypeFieldStillAvailable( $content )
    {
        foreach ( $content->getFields() as $field )
        {
            if ( $field->fieldDefIdentifier === $this->customFieldIdentifier )
            {
                return $field;
            }
        }

        $this->fail( "Custom field not found." );
    }

    /**
     * @depends testUpdateTypeFieldStillAvailable
     */
    public function testUpdatedDataCorrect( Field $field )
    {
        $this->assertUpdatedFieldDataLoadedCorrect( $field );
    }

    /**
     * @depends testCreateContent
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCopyContent
     */
    public function testCopyField( $content )
    {
        $content = $this->testCreateContent();

        $repository     = $this->getRepository();
        $contentService = $repository->getContentService();

        $locationService  = $repository->getLocationService();
        $parentLocationId = $this->generateId( 'location', 2 );
        $locationCreate   = $locationService->newLocationCreateStruct( $parentLocationId );

        $copied = $contentService->copyContent( $content->contentInfo, $locationCreate );

        $this->assertNotSame(
            $content->contentInfo->id,
            $copied->contentInfo->id
        );

        return $contentService->loadContent( $copied->id );
    }

    /**
     * @depends testCopyField
     */
    public function testCopiedFieldType( $content )
    {
        foreach ( $content->getFields() as $field )
        {
            if ( $field->fieldDefIdentifier === $this->customFieldIdentifier )
            {
                return $field;
            }
        }

        $this->fail( "Custom field not found." );
    }

    /**
     * @depends testCopiedFieldType
     */
    public function testCopiedExternalData( Field $field )
    {
        $this->assertCopiedFieldDataLoadedCorrectly( $field );
    }

    /**
     * @depends testCopyField
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentServiceTest::deleteContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteContent( $content )
    {
        $content = $this->testPublishContent();

        $repository     = $this->getRepository();
        $contentService = $repository->getContentService();

        $contentService->deleteContent( $content->contentInfo );

        $contentService->loadContent( $content->contentInfo->id );
    }

    /**
     * Tests failing content creation
     *
     * @param mixed $failingValue
     * @param string $expectedException
     *
     * @dataProvider provideInvalidCreationFieldData
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteContent
     *
     * @return void
     */
    public function testCreateContentFails( $failingValue, $expectedException )
    {
        try
        {
            $this->createContent( $failingValue );

            $this->fail( 'Expected exception not thrown.' );
        }
        catch ( \PHPUnit_Framework_AssertionFailedError $e )
        {
            throw $e;
        }
        catch ( \Exception $e )
        {
            $this->assertInstanceOf(
                $expectedException,
                $e
            );
        }
    }

    /**
     * Tests failing content update
     *
     * @param mixed $failingValue
     * @param string $expectedException
     *
     * @dataProvider provideInvalidUpdateFieldData
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     *
     * @return void
     */
    public function testUpdateContentFails( $failingValue, $expectedException )
    {
        try
        {
            $this->updateContent( $failingValue );

            $this->fail( 'Expected exception not thrown.' );
        }
        catch ( \PHPUnit_Framework_AssertionFailedError $e )
        {
            throw $e;
        }
        catch ( \Exception $e )
        {
            $this->assertInstanceOf(
                $expectedException,
                $e
            );
        }
    }

    /**
     * @dataProvider provideToHashData
     */
    public function testToHash( $value, $expectedHash )
    {
        $repository       = $this->getRepository();
        $fieldTypeService = $repository->getFieldTypeService();
        $fieldType = $fieldTypeService->getFieldType( $this->getTypeName() );

        $this->assertEquals(
            $expectedHash,
            $fieldType->toHash( $value )
        );
    }

    /**
     * @depends testCreateContent
     * @dataProvider provideFromHashData
     * @todo: Requires correct registered FieldTypeService, needs to be
     *        maintained!
     */
    public function testFromHash( $hash, $expectedValue )
    {
        $repository       = $this->getRepository();
        $fieldTypeService = $repository->getFieldTypeService();
        $fieldType        = $fieldTypeService->getFieldType( $this->getTypeName() );

        $this->assertEquals(
            $expectedValue,
            $fieldType->fromHash( $hash )
        );
    }
}
