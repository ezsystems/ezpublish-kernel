<?php
/**
 * File contains: eZ\Publish\SPI\Tests\FieldType\BaseIntegrationTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\Persistence;
use eZ\Publish\Core\Persistence\TransformationProcessor\DefinitionBased;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Publish\Core\Base\Container\Compiler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Integration test for the legacy storage
 *
 * @group integration
 */
abstract class BaseIntegrationTest extends TestCase
{
    /**
     * Property indicating whether the DB already has been set up
     *
     * @var boolean
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
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected static $container;

    /**
     * @return string
     */
    static protected function getInstallationDir()
    {
        static $installDir = null;
        if ( $installDir === null )
        {
            $config = require __DIR__ . '/../../../../../config.php';
            $installDir = $config['install_dir'];
        }
        return $installDir;
    }

    /**
     * @var \eZ\Publish\Core\Persistence\TransformationProcessor
     */
    protected $transformationProcessor;

    /**
     * @return \eZ\Publish\Core\Persistence\TransformationProcessor
     */
    public function getTransformationProcessor()
    {
        if ( !isset( $this->transformationProcessor ) )
        {
            $this->transformationProcessor = new DefinitionBased(
                new Persistence\TransformationProcessor\DefinitionBased\Parser( self::getInstallationDir() ),
                new Persistence\TransformationProcessor\PcreCompiler( new Persistence\Utf8Converter() ),
                glob( __DIR__ . '/../../../Core/Persistence/Tests/TransformationProcessor/_fixtures/transformations/*.tr' )
            );
        }

        return $this->transformationProcessor;
    }

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
     * FieldType\Storage has been registered.
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
     *
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
     *
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
            self::$container = $this->getContainer();
            $this->handler = self::$container->get( "ezpublish.api.storage_engine.legacy.dbhandler" );
            $this->db = $this->handler->getName();
            parent::setUp();
            $this->insertDatabaseFixture( __DIR__ . '/../../../Core/Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php' );
            self::$setUp = $this->handler;
        }
        else
        {
            $this->handler = self::$setUp;
        }
    }

    public function testCreateContentType()
    {
        $contentType = $this->createContentType();

        $this->assertNotNull( $contentType->id );
        self::$contentTypeId = $contentType->id;

        return $contentType;
    }

    /**
     * Performs the creation of the content type with a field of the field type
     * under test
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    protected function createContentType()
    {
        $createStruct = new Content\Type\CreateStruct(
            array(
                'name'              => array( 'eng-GB' => 'Test' ),
                'identifier'        => 'test-' . $this->getTypeName(),
                'status'            => 0,
                'creatorId'         => 14,
                'created'           => time(),
                'modifierId'        => 14,
                'modified'          => time(),
                'initialLanguageId' => 2,
                'remoteId'          => 'abcdef',
            )
        );

        $createStruct->fieldDefinitions = array(
            new Content\Type\FieldDefinition(
                array(
                    'name'           => array( 'eng-GB' => 'Name' ),
                    'identifier'     => 'name',
                    'fieldGroup'     => 'main',
                    'position'       => 1,
                    'fieldType'      => 'ezstring',
                    'isTranslatable' => false,
                    'isRequired'     => true,
                )
            ),
            new Content\Type\FieldDefinition(
                array(
                    'name'           => array( 'eng-GB' => 'Data' ),
                    'identifier'     => 'data',
                    'fieldGroup'     => 'main',
                    'position'       => 2,
                    'fieldType'      => $this->getTypeName(),
                    'isTranslatable' => false,
                    'isRequired'     => true,
                    'fieldTypeConstraints' => $this->getTypeConstraints(),
                )
            ),
        );

        $handler            = $this->getCustomHandler();
        $contentTypeHandler = $handler->contentTypeHandler();

        return $contentTypeHandler->create( $createStruct );
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
        $handler = $this->getCustomHandler();

        $content = $this->createContent( $contentType, $this->getInitialValue() );

        self::$contentId      = $content->versionInfo->contentInfo->id;
        self::$contentVersion = $content->versionInfo->contentInfo->currentVersionNo;

        $this->postCreationHook( $handler, $content );

        return $content;
    }

    /**
     * Creates content of the given $contentType with $fieldValue in
     * $languageCode
     *
     * @param Type $contentType
     * @param mixed $fieldValue
     * @param string $languageCode
     *
     * @return Content
     */
    protected function createContent( Type $contentType, $fieldValue, $languageCode = 'eng-GB' )
    {
        $createStruct = new Content\CreateStruct(
            array(
                'name'              => array( $languageCode => 'Test object' ),
                'typeId'            => $contentType->id,
                'sectionId'         => 1,
                'ownerId'           => 14,
                'locations'         => array(
                    new Content\Location\CreateStruct(
                        array(
                            'parentId' => 2,
                            'remoteId' => 'sindelfingen',
                        )
                    )
                ),
                // Language with id=2 is eng-US
                // This is probably a mistake, as the fields are given with eng-GB, but it has a nice
                // side effect of testing creation with empty value.
                // TODO: change to eng-GB (8) and/or find a more obvious way to test creation with empty value
                'initialLanguageId' => 2,
                'remoteId'          => microtime(),
                'modified'          => time(),
                'fields'            => array(
                    new Content\Field(
                        array(
                            'type'              => 'ezstring',
                            'languageCode'      => $languageCode,
                            'fieldDefinitionId' => $contentType->fieldDefinitions[0]->id,
                            'value'             => new Content\FieldValue(
                                array(
                                    'data'    => 'This is just a test object',
                                    'sortKey' => 'this is just a test object',
                                )
                            ),
                        )
                    ),
                    new Content\Field(
                        array(
                            'type'              => $this->getTypeName(),
                            'languageCode'      => $languageCode,
                            'fieldDefinitionId' => $contentType->fieldDefinitions[1]->id,
                            'value'             => $fieldValue,
                        )
                    ),
                ),
            )
        );

        $handler = $this->getCustomHandler();
        $contentHandler = $handler->contentHandler();
        return $contentHandler->create( $createStruct );
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
        $field->value = $this->getUpdatedValue();

        return $this->updateContent( self::$contentId, self::$contentVersion, $field );
    }

    /**
     * Performs an update on $contentId in $contentVersion setting $field
     *
     * @param mixed $contentId
     * @param mixed $contentVersion
     * @param Field $field
     *
     * @return Content
     */
    protected function updateContent( $contentId, $contentVersion, Field $field )
    {
        $handler = $this->getCustomHandler();

        $field->value = $this->getUpdatedValue();
        $updateStruct = new UpdateStruct(
            array(
                'creatorId' => 14,
                'modificationDate' => time(),
                'initialLanguageId' => 2,
                'fields' => array(
                    $field,
                )
            )
        );

        $contentHandler = $handler->contentHandler();
        return $contentHandler->updateContent( $contentId, $contentVersion, $updateStruct );
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

        $this->deleteContent( $content );

        $this->assertDeletedFieldDataCorrect( $content );

        $contentHandler->load(
            $content->versionInfo->contentInfo->id,
            $content->versionInfo->versionNo
        );
    }

    /**
     * Deletes the given $content
     *
     * @param Content $content
     *
     * @return void
     */
    protected function deleteContent( Content $content )
    {
        $handler        = $this->getCustomHandler();
        $contentHandler = $handler->contentHandler();

        $contentHandler->removeRawContent(
            $content->versionInfo->contentInfo->id
        );
    }

    protected function getContainer()
    {
        $config = include __DIR__ . "/../../../../../config.php";
        $installDir = $config["install_dir"];

        $containerBuilder = new ContainerBuilder();
        $settingsPath = $installDir . "/eZ/Publish/Core/settings/";
        $loader = new YamlFileLoader( $containerBuilder, new FileLocator( $settingsPath ) );

        $loader->load( 'fieldtypes.yml' );
        $loader->load( 'io.yml' );
        $loader->load( 'repository.yml' );
        $loader->load( 'fieldtype_external_storages.yml' );
        $loader->load( 'storage_engines/common.yml' );
        $loader->load( 'storage_engines/shortcuts.yml' );
        $loader->load( 'storage_engines/legacy.yml' );
        $loader->load( 'search_engines/legacy.yml' );
        $loader->load( 'storage_engines/cache.yml' );
        $loader->load( 'settings.yml' );
        $loader->load( 'fieldtype_services.yml' );
        $loader->load( 'utils.yml' );

        $containerBuilder->setParameter( "ezpublish.kernel.root_dir", $installDir );

        $containerBuilder->setParameter(
            "legacy_dsn",
            $this->getDsn()
        );

        $containerBuilder->compile();

        return $containerBuilder;
    }

    /**
     * Returns the Handler
     *
     * @param string $identifier
     * @param \eZ\Publish\SPI\Persistence\FieldType $fieldType
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter $fieldValueConverter
     * @param \eZ\Publish\SPI\FieldType\FieldStorage $externalStorage
     *
     * @return \eZ\Publish\SPI\Persistence\Handler
     */
    protected function getHandler( $identifier, $fieldType, $fieldValueConverter, $externalStorage )
    {
        /** @var \eZ\Publish\Core\Persistence\FieldTypeRegistry $fieldTypeRegistry */
        $fieldTypeRegistry = self::$container->get( "ezpublish.persistence.field_type_registry" );
        /** @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry $converterRegistry */
        $converterRegistry = self::$container->get( "ezpublish.persistence.legacy.field_value_converter.registry" );
        /** @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry $storageRegistry */
        $storageRegistry = self::$container->get( "ezpublish.persistence.external_storage_registry" );

        $textLineFieldType = new \eZ\Publish\Core\FieldType\TextLine\Type();
        $textLineFieldType->setTransformationProcessor( $this->getTransformationProcessor() );
        $textLineFieldValueConverter = new Legacy\Content\FieldValue\Converter\TextLine();

        $fieldTypeRegistry->register( "ezstring", $textLineFieldType );
        $converterRegistry->register( "ezstring", $textLineFieldValueConverter );

        $fieldTypeRegistry->register( $identifier, $fieldType );
        $converterRegistry->register( $identifier, $fieldValueConverter );
        $storageRegistry->register( $identifier, $externalStorage );

        return self::$container->get( "ezpublish.spi.persistence.legacy" );
    }
}
