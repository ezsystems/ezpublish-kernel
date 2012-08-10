<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\HandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Tests\FieldType;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry;

/**
 * Integration test for the legacy storage
 *
 * @group integration
 */
abstract class BaseIntegrationTest extends TestCase
{
    /**
     * Property indicating wether the DB already has been set up
     *
     * @var bool
     */
    protected static $setUp = false;

    /**
     * Id of test content type
     *
     * @var string
     */
    protected static $contentTypeId;

    /**
     * Id of test content
     *
     * @var string
     */
    protected static $contentId;

    /**
     * Current version of test content
     *
     * @var string
     */
    protected static $contentVersion;

    /**
     * Returns the identifier of the FieldType under test
     *
     * @return string
     */
    abstract public function getTypeName();

    /**
     * Returns the Handler with all necessary objects registered
     *
     * Returns an instance of the Persistence Handler where the
     * FieldTypy\Storage has been registered.
     *
     * @return \eZ\Publish\SPI\Persistence\Handler
     */
    abstract public function getCustomHandler();

    /**
     * Returns the FieldTypeConstraints to be used to create a field definition
     * of the FieldType under test.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints
     */
    abstract public function getTypeConstraints();

    /**
     * Returns the field definition data expected after loading the newly
     * created field definition with the FieldType under test
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    abstract public function getFieldDefinitionData();

    /**
     * Get initial field value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    abstract public function getInitialValue();

    /**
     * Asserts that the loaded field data is correct
     *
     * Performs assertions on the loaded field, mainly checking that the
     * $field->value->externalData is loaded correctly. If the loading of
     * external data manipulates other aspects of $field, their correctness
     * also needs to be asserted. Make sure you implement this method agnostic
     * to the used SPI\Persistence implementation!
     */
    public function assertLoadedFieldDataCorrect( Field $field )
    {
        $this->assertEquals(
            $this->getInitialValue(),
            $field->value
        );
    }

    /**
     * Get update field value.
     *
     * Use to update the field
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    abstract public function getUpdatedValue();

    /**
     * Asserts that the updated field data is loaded correct
     *
     * Performs assertions on the loaded field after it has been updated,
     * mainly checking that the $field->value->externalData is loaded
     * correctly. If the loading of external data manipulates other aspects of
     * $field, their correctness also needs to be asserted. Make sure you
     * implement this method agnostic to the used SPI\Persistence
     * implementation!
     *
     * @return void
     */
    public function assertUpdatedFieldDataCorrect( Field $field )
    {
        $this->assertEquals(
            $this->getUpdatedValue(),
            $field->value
        );
    }

    /**
     * Method called after content creation
     *
     * Useful, if additional stuff should be executed (like creating the actual
     * user).
     *
     * @param Legacy\Handler $handler
     * @param Content $content
     * @return void
     */
    public function postCreationHook( Legacy\Handler $handler, Content $content )
    {
        // Do nothing by default
    }

    /**
     * Can be overwritten to assert that additional data has been deleted
     *
     * @param Content $content
     * @return void
     */
    public function assertDeletedFieldDataCorrect( Content $content )
    {
        // Do nothing by default
    }

    /**
     * Only set up once for these read only tests on a large fixture
     *
     * Skipping the reset-up, since setting up for these tests takes quite some
     * time, which is not required to spent, since we are only reading from the
     * database anyways.
     *
     * @return void
     */
    public function setUp()
    {
        if ( !self::$setUp )
        {
            parent::setUp();
            $this->insertDatabaseFixture( __DIR__ . '/../../../Core/Persistence/Legacy/Tests/Content/SearchHandler/_fixtures/full_dump.php' );
            self::$setUp = $this->handler;
        }
        else
        {
            $this->handler = self::$setUp;
        }
    }

    public function testCreateContentType()
    {
        $createStruct = new Content\Type\CreateStruct( array(
            'name'              => array( 'eng-GB' => 'Test' ),
            'identifier'        => 'test-' . $this->getTypeName(),
            'status'            => 0,
            'creatorId'         => 14,
            'created'           => time(),
            'modifierId'        => 14,
            'modified'          => time(),
            'initialLanguageId' => 2,
            'remoteId'          => 'abcdef',
        ) );

        $createStruct->fieldDefinitions = array(
            new Content\Type\FieldDefinition( array(
                'name'           => array( 'eng-GB' => 'Name' ),
                'identifier'     => 'name',
                'fieldGroup'     => 'main',
                'position'       => 1,
                'fieldType'      => 'ezstring',
                'isTranslatable' => false,
                'isRequired'     => true,
            ) ),
            new Content\Type\FieldDefinition( array(
                'name'           => array( 'eng-GB' => 'Data' ),
                'identifier'     => 'data',
                'fieldGroup'     => 'main',
                'position'       => 2,
                'fieldType'      => $this->getTypeName(),
                'isTranslatable' => false,
                'isRequired'     => true,
                'fieldTypeConstraints' => $this->getTypeConstraints(),
            ) ),
        );

        $handler            = $this->getCustomHandler();
        $contentTypeHandler = $handler->contentTypeHandler();

        $contentType = $contentTypeHandler->create( $createStruct );

        $this->assertNotNull( $contentType->id );
        self::$contentTypeId = $contentType->id;

        return $contentType;
    }

    /**
     * @depends testCreateContentType
     */
    public function testContentTypeField( $contentType )
    {
        $this->assertSame(
            $this->getTypeName(),
            $contentType->fieldDefinitions[1]->fieldType
        );
    }

    /**
     * @depends testCreateContentType
     */
    public function testLoadContentTypeField()
    {
        $handler            = $this->getCustomHandler();
        $contentTypeHandler = $handler->contentTypeHandler();

        return $contentTypeHandler->load( self::$contentTypeId );
    }

    /**
     * @depends testLoadContentTypeField
     */
    public function testLoadContentTypeFieldType( $contentType )
    {
        $this->assertSame(
            $this->getTypeName(),
            $contentType->fieldDefinitions[1]->fieldType
        );

        return $contentType->fieldDefinitions[1];
    }

    /**
     * @depends testLoadContentTypeFieldType
     * @dataProvider getFieldDefinitionData
     */
    public function testLoadContentTypeFieldData( $name, $value, $field )
    {
        $this->assertEquals(
            $value,
            $field->$name
        );
    }

    /**
     * @depends testLoadContentTypeField
     */
    public function testCreateContent( $contentType )
    {
        $createStruct = new Content\CreateStruct( array(
            'name'              => array( 'eng-GB' => 'Test object' ),
            'typeId'            => $contentType->id,
            'sectionId'         => 1,
            'ownerId'           => 14,
            'locations'         => array( new Content\Location\CreateStruct( array(
                'parentId' => 2,
                'remoteId' => 'sindelfingen',
            ) ) ),
            'initialLanguageId' => 2,
            'remoteId'          => microtime(),
            'modified'          => time(),
            'fields'            => array(
                new Content\Field( array(
                    'type'              => 'ezstring',
                    'languageCode'      => 'eng-GB',
                    'fieldDefinitionId' => $contentType->fieldDefinitions[0]->id,
                    'value'             => new Content\FieldValue( array(
                        'data'    => 'This is just a test object',
                        'sortKey' => 'this is just a test object',
                    ) ),
                ) ),
                new Content\Field( array(
                    'type'              => $this->getTypeName(),
                    'languageCode'      => 'eng-GB',
                    'fieldDefinitionId' => $contentType->fieldDefinitions[1]->id,
                    'value'             => $this->getInitialValue(),
                ) ),
            ),
        ) );

        $handler = $this->getCustomHandler();
        $contentHandler = $handler->contentHandler();

        $content = $contentHandler->create( $createStruct );
        self::$contentId      = $content->contentInfo->id;
        self::$contentVersion = $content->contentInfo->currentVersionNo;

        $this->postCreationHook( $handler, $content );

        return $content;
    }

    /**
     * @depends testCreateContent
     */
    public function testCreatedFieldType( $content )
    {
        $this->assertSame(
            $this->getTypeName(),
            $content->fields[1]->type
        );

        return $content->fields[1];
    }

    /**
     * @depends testCreateContent
     */
    public function testLoadField()
    {
        $handler = $this->getCustomHandler();

        $contentHandler = $handler->contentHandler();
        return $contentHandler->load( self::$contentId, self::$contentVersion );
    }

    /**
     * @depends testLoadField
     */
    public function testLoadFieldType( $content )
    {
        $this->assertSame(
            $this->getTypeName(),
            $content->fields[1]->type
        );

        return $content->fields[1];
    }

    /**
     * @depends testLoadFieldType
     */
    public function testLoadExternalData( $field )
    {
        $this->assertLoadedFieldDataCorrect( $field );
    }

    /**
     * @depends testLoadFieldType
     */
    public function testUpdateField( $field )
    {
        $handler = $this->getCustomHandler();

        $field->value = $this->getUpdatedValue();
        $updateStruct = new \eZ\Publish\SPI\Persistence\Content\UpdateStruct( array(
            'creatorId' => 14,
            'modificationDate' => time(),
            'initialLanguageId' => 2,
            'fields' => array(
                $field,
            )
        ) );

        $contentHandler = $handler->contentHandler();
        return $contentHandler->updateContent( self::$contentId, self::$contentVersion, $updateStruct );
    }

    /**
     * @depends testUpdateField
     */
    public function testUpdateFieldType( $content )
    {
        $this->assertSame(
            $this->getTypeName(),
            $content->fields[1]->type
        );

        return $content->fields[1];
    }

    /**
     * @depends testUpdateFieldType
     */
    public function testUpdateExternalData( $field )
    {
        $this->assertUpdatedFieldDataCorrect( $field );
    }

    /**
     * @depends testUpdateField
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteField( $content )
    {
        $handler        = $this->getCustomHandler();
        $contentHandler = $handler->contentHandler();

        $contentHandler->removeRawContent(
            $content->versionInfo->contentId
        );

        $this->assertDeletedFieldDataCorrect( $content );

        $contentHandler->load(
            $content->versionInfo->contentId,
            $content->versionInfo->versionNo
        );
    }

    /**
     * Returns the Handler
     *
     * @return Handler
     */
    protected function getHandler()
    {
        return new Legacy\Handler(
            self::$setUp,
            new ConverterRegistry(
                array(
                    'ezstring' => new Legacy\Content\FieldValue\Converter\TextLine(),
                )
            ),
            new StorageRegistry(
                array()
            ),
            $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Search\\TransformationProcessor',
                array(),
                array(),
                '',
                false
            )
        );
    }
}
