<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\Gateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Language,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\Version,
    eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase.
 */
class EzcDatabaseTest extends LanguageAwareTestCase
{
    /**
     * Database gateway to test.
     *
     * @var eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase
     */
    protected $databaseGateway;

    /**
     * Language mask generator
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Language handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingLanguageHandler
     */
    protected $languageHandler;

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::__construct
     */
    public function testCtor()
    {
        $handlerMock = $this->getDatabaseHandler();
        $gateway = $this->getDatabaseGateway();

        $this->assertAttributeSame(
            $handlerMock,
            'dbHandler',
            $gateway
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::insertContentObject
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::generateLanguageMask
     * @todo Fix not available fields
     */
    public function testInsertContentObject()
    {
        $struct = $this->getCreateStructFixture();

        $gateway = $this->getDatabaseGateway();
        $gateway->insertContentObject( $struct );

        $this->assertQueryResult(
            array(
                array(
                    'name' => 'Content name',
                    'contentclass_id' => '23',
                    'section_id' => '42',
                    'owner_id' => '13',
                    'current_version' => '1',
                    'initial_language_id' => '1',
                    'remote_id' => 'some_remote_id',
                    'language_mask' => '1',
                    'modified' => '456',
                    'published' => '123',
                    'status' => ContentInfo::STATUS_DRAFT,
                ),
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    array(
                        'name',
                        'contentclass_id',
                        'section_id',
                        'owner_id',
                        'current_version',
                        'initial_language_id',
                        'remote_id',
                        'language_mask',
                        'modified',
                        'published',
                        'status',
                    )
                )->from( 'ezcontentobject' )
        );
    }

    /**
     * Returns a Content fixture
     *
     * @return eZ\Publish\SPI\Persistence\Content\CreateStruct
     */
    protected function getCreateStructFixture()
    {
        $struct = new CreateStruct();

        $struct->typeId = 23;
        $struct->sectionId = 42;
        $struct->ownerId = 13;
        $struct->initialLanguageId = 1;
        $struct->remoteId = 'some_remote_id';
        $struct->alwaysAvailable = true;
        $struct->published = 123;
        $struct->modified = 456;
        $struct->name = array(
            'always-available' => 'eng-US',
            'eng-US' => 'Content name',
        );
        $struct->fields = array();
        $struct->locations = array();

        return $struct;
    }

    /**
     * Returns a Content fixture
     *
     * @return eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentFixture()
    {
        $content = new Content;

        $content->contentInfo = new ContentInfo;
        $content->contentInfo->contentTypeId = 23;
        $content->contentInfo->sectionId = 42;
        $content->contentInfo->ownerId = 13;
        $content->contentInfo->currentVersionNo = 2;
        $content->contentInfo->mainLanguageCode = 'eng-US';
        $content->contentInfo->remoteId = 'some_remote_id';
        $content->contentInfo->isAlwaysAvailable = true;
        $content->contentInfo->publicationDate = 123;
        $content->contentInfo->modificationDate = 456;
        $content->contentInfo->isPublished = false;
        $content->contentInfo->name = 'Content name';

        $content->versionInfo = new VersionInfo;
        $content->versionInfo->names = array(
            'always-available' => 'eng-US',
            'eng-US' => 'Content name',
        );
        $content->versionInfo->status = VersionInfo::STATUS_PENDING;
        $content->locations = array();

        return $content;
    }

    /**
     * Returns a Version fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    protected function getVersionFixture()
    {
        $version = new VersionInfo;

        $version->id = null;
        $version->versionNo = 1;
        $version->creatorId = 13;
        $version->status = 0;
        $version->contentId = 2342;
        $version->creationDate = 1312278322;
        $version->modificationDate = 1312278323;
        $version->initialLanguageCode = 'eng-GB';

        return $version;
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::insertVersion
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::generateLanguageMask
     */
    public function testInsertVersion()
    {
        $version = $this->getVersionFixture();

        $gateway = $this->getDatabaseGateway();
        $this->languageHandler
            ->expects( $this->once() )
            ->method( 'getByLocale' )
            ->with( 'eng-GB' )
            ->will(
                $this->returnValue(
                    new Language(
                        array(
                            'id' => 2,
                            'languageCode' => 'eng-GB',
                        )
                    )
                )
            );
        $gateway->insertVersion( $version, array(), true );

        $this->assertQueryResult(
            array(
                array(
                    'contentobject_id' => '2342',
                    'created' => '1312278322',
                    'creator_id' => '13',
                    'modified' => '1312278323',
                    'status' => '0',
                    'workflow_event_pos' => '0',
                    'version' => '1',
                    'language_mask' => '1',
                    'initial_language_id' => '2',
                    // Not needed, according to field mapping document
                    // 'user_id',
                )
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    array(
                        'contentobject_id',
                        'created',
                        'creator_id',
                        'modified',
                        'status',
                        'workflow_event_pos',
                        'version',
                        'language_mask',
                        'initial_language_id',
                    )
                )->from( 'ezcontentobject_version' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::setStatus
     */
    public function testSetStatus()
    {
        $gateway = $this->getDatabaseGateway();
        $this->languageHandler
            ->expects( $this->once() )
            ->method( 'getByLocale' )
            ->with( 'eng-GB' )
            ->will(
                $this->returnValue(
                    new Language(
                        array(
                            'id' => 2,
                            'languageCode' => 'eng-GB',
                        )
                    )
                )
            );

        // insert content
        $struct = $this->getCreateStructFixture();
        $contentId = $gateway->insertContentObject( $struct );

        // insert version
        $version = $this->getVersionFixture();
        $version->contentId = $contentId;
        $gateway->insertVersion( $version, array(), true );

        $this->assertTrue(
            $gateway->setStatus( $version->contentId, $version->versionNo, VersionInfo::STATUS_PENDING )
        );

        $this->assertQueryResult(
            array( array( VersionInfo::STATUS_PENDING ) ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select( 'status' )
                ->from( 'ezcontentobject_version' )
        );

        // check that content status has not been set to published
        $this->assertQueryResult(
            array( array( VersionInfo::STATUS_DRAFT ) ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select( 'status' )
                ->from( 'ezcontentobject' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::setStatus
     */
    public function testSetStatusPublished()
    {
        $gateway = $this->getDatabaseGateway();
        $this->languageHandler
            ->expects( $this->once() )
            ->method( 'getByLocale' )
            ->with( 'eng-GB' )
            ->will(
                $this->returnValue(
                    new Language(
                        array(
                            'id' => 2,
                            'languageCode' => 'eng-GB',
                        )
                    )
                )
            );

        // insert content
        $struct = $this->getCreateStructFixture();
        $contentId = $gateway->insertContentObject( $struct );

        // insert version
        $version = $this->getVersionFixture();
        $version->contentId = $contentId;
        $gateway->insertVersion( $version, array(), true );

        $this->assertTrue(
            $gateway->setStatus( $version->contentId, $version->versionNo, VersionInfo::STATUS_PUBLISHED )
        );

        $this->assertQueryResult(
            array( array( VersionInfo::STATUS_PUBLISHED ) ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select( 'status' )
                ->from( 'ezcontentobject_version' )
        );

        // check that content status has been set to published
        $this->assertQueryResult(
            array( array( ContentInfo::STATUS_PUBLISHED ) ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select( 'status' )
                ->from( 'ezcontentobject' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::setStatus
     */
    public function testSetStatusUnknownVersion()
    {
        $gateway = $this->getDatabaseGateway();

        $this->assertFalse(
            $gateway->setStatus( 23, 42, 2 )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::updateContent
     */
    public function testUpdateContent()
    {
        $gateway = $this->getDatabaseGateway();

        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $metadataStruct = $this->getMetadataUpdateStructFixture();

        $gateway->updateContent( 10, $metadataStruct );

        $this->assertQueryResult(
            array(
                array(
                    'initial_language_id' => '3',
                    'modified' => '234567',
                    'owner_id' => '42',
                    'published' => '123456'
                )
            ),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select(
                    'initial_language_id',
                    'modified',
                    'owner_id',
                    'published'
                )->from( 'ezcontentobject' )
                ->where( 'id = 10' )
        );
    }

    /**
     * Returns an UpdateStruct fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UpdateStruct
     */
    protected function getUpdateStructFixture()
    {
        $struct = new UpdateStruct();
        $struct->creatorId = 23;
        $struct->fields = array();
        $struct->modificationDate = 234567;
        $struct->initialLanguageId = 3;
        return $struct;
    }

    /**
     * Returns a MetadataUpdateStruct fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct
     */
    protected function getMetadataUpdateStructFixture()
    {
        $struct = new MetadataUpdateStruct();
        $struct->ownerId = 42;
        $struct->publicationDate = 123456;
        $struct->mainLanguageId = 3;
        $struct->modificationDate = 234567;
        return $struct;
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::updateVersion
     * @return void
     */
    public function testUpdateVersion()
    {
        $gateway = $this->getDatabaseGateway();

        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway->updateVersion( 10, 2, $this->getUpdateStructFixture() );

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $this->assertQueryResult(
            array(
                array(
                    'initial_language_id' => '3',
                    'modified' => '234567',
                )
            ),
            $query
                ->select(
                    array(
                        'initial_language_id',
                        'modified',
                    )
                )->from( 'ezcontentobject_version' )
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq( 'contentobject_id', 10 ),
                        $query->expr->eq( 'version', 2 )
                    )
                )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::insertNewField
     * @return void
     */
    public function testInsertNewField()
    {
        $content = $this->getContentFixture();
        $content->contentInfo->contentId = 2342;
        // $content->versionInfo->versionNo = 3;

        $field = $this->getFieldFixture();
        $value = $this->getStorageValueFixture();

        $gateway = $this->getDatabaseGateway();
        $gateway->insertNewField( $content, $field, $value );

        $this->assertQueryResult(
            array(
                array(
                    'contentclassattribute_id' => '231',
                    'contentobject_id' => '2342',
                    'data_float' => '24.42',
                    'data_int' => '42',
                    'data_text' => 'Test text',
                    'data_type_string' => 'ezstring',
                    'language_code' => 'eng-GB',
                    'language_id' => '4',
                    'sort_key_int' => '23',
                    'sort_key_string' => 'Test',
                    'version' => '1',
                    'language_id' => '5',
                )
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    array(
                        'contentclassattribute_id',
                        'contentobject_id',
                        'data_float',
                        'data_int',
                        'data_text',
                        'data_type_string',
                        'language_code',
                        'language_id',
                        'sort_key_int',
                        'sort_key_string',
                        'version',
                        'language_id',
                    )
                )->from( 'ezcontentobject_attribute' )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::updateField
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::setFieldUpdateValues
     * @return void
     */
    public function testUpdateField()
    {
        $content = $this->getContentFixture();
        $content->contentInfo->contentId = 2342;

        $field = $this->getFieldFixture();
        $value = $this->getStorageValueFixture();

        $gateway = $this->getDatabaseGateway();
        $field->id = $gateway->insertNewField( $content, $field, $value );

        $newValue = new StorageFieldValue(
            array(
                'dataFloat' => 124.42,
                'dataInt' => 142,
                'dataText' => 'New text',
                'sortKeyInt' => 123,
                'sortKeyString' => 'new_text',
            )
        );

        $gateway->updateField( $field, $newValue );

        $this->assertQueryResult(
            array(
                array(
                    'data_float' => '124.42',
                    'data_int' => '142',
                    'data_text' => 'New text',
                    'sort_key_int' => '123',
                    'sort_key_string' => 'new_text',
                )
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    array(
                        'data_float',
                        'data_int',
                        'data_text',
                        'sort_key_int',
                        'sort_key_string',
                    )
                )->from( 'ezcontentobject_attribute' )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::updateNonTranslatableField
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::setFieldUpdateValues
     * @return void
     */
    public function testUpdateNonTranslatableField()
    {
        $content = $this->getContentFixture();
        $content->contentInfo->contentId = 2342;

        $fieldGb = $this->getFieldFixture();
        $fieldUs = $this->getOtherLanguageFieldFixture();
        $value = $this->getStorageValueFixture();

        $gateway = $this->getDatabaseGateway();
        $fieldGb->id = $gateway->insertNewField( $content, $fieldGb, $value );
        $fieldUs->id = $gateway->insertNewField( $content, $fieldUs, $value );

        $updateStruct = new Content\UpdateStruct();

        $newValue = new StorageFieldValue(
            array(
                'dataFloat' => 124.42,
                'dataInt' => 142,
                'dataText' => 'New text',
                'sortKeyInt' => 123,
                'sortKeyString' => 'new_text',
            )
        );

        $gateway->updateNonTranslatableField( $fieldGb, $newValue, $content->contentInfo->contentId );

        $this->assertQueryResult(
            array(
                // Both fields updated
                array(
                    'data_float' => '124.42',
                    'data_int' => '142',
                    'data_text' => 'New text',
                    'sort_key_int' => '123',
                    'sort_key_string' => 'new_text',
                ),
                array(
                    'data_float' => '124.42',
                    'data_int' => '142',
                    'data_text' => 'New text',
                    'sort_key_int' => '123',
                    'sort_key_string' => 'new_text',
                )
            ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    array(
                        'data_float',
                        'data_int',
                        'data_text',
                        'sort_key_int',
                        'sort_key_string',
                    )
                )->from( 'ezcontentobject_attribute' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::listVersions
     */
    public function testListVersions()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $res = $gateway->listVersions( 226 );

        $this->assertEquals(
            3,
            count( $res )
        );

        foreach ( $res as $row )
        {
            $this->assertEquals(
                12,
                count( $row )
            );
        }

        $this->assertEquals(
            675,
            $res[0]['ezcontentobject_version_id']
        );
        $this->assertEquals(
            676,
            $res[1]['ezcontentobject_version_id']
        );
        $this->assertEquals(
            676,
            $res[2]['ezcontentobject_version_id']
        );

        /*
        $this->storeFixture(
            __DIR__ . '/../_fixtures/restricted_version_rows.php',
            $res
        );
        */
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::listVersionsForUser
     */
    public function testListVersionsForUser()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $res = $gateway->listVersionsForUser( 14 );

        $this->assertEquals(
            2,
            count( $res )
        );

        foreach ( $res as $row )
        {
            $this->assertEquals(
                12,
                count( $row )
            );
        }

        $this->assertEquals(
            677,
            $res[0]['ezcontentobject_version_id']
        );
        $this->assertEquals(
            0,
            $res[0]['ezcontentobject_version_status']
        );
        $this->assertEquals(
            678,
            $res[1]['ezcontentobject_version_id']
        );
        $this->assertEquals(
            0,
            $res[1]['ezcontentobject_version_status']
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::load
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder
     */
    public function testLoadWithAllTranslations()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $this->languageHandler
            ->expects( $this->any() )
            ->method( 'getById' )
            ->will(
                $this->returnValue(
                    new Language(
                        array(
                            'id' => 4,
                            'languageCode' => 'eng-US',
                        )
                    )
                )
            );
        $res = $gateway->load( 226, 2 );

        $this->assertValuesInRows(
            'ezcontentobject_attribute_language_code',
            array( 'eng-US', 'eng-GB' ),
            $res
        );

        $this->assertValuesInRows(
            'ezcontentobject_attribute_language_id',
            array( '2' ),
            $res
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::load
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder
    public function testCreateFixtureForMapperExtractContentFromRowsMultipleVersions()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $resFirst  = $gateway->load( 11, 1 );
        $resSecond = $gateway->load( 11, 2 );

        $res = array_merge( $resFirst, $resSecond );

        $this->storeFixture(
            __DIR__ . '/../_fixtures/extract_content_from_rows_multiple_versions.php',
            $res
        );
    }
     */

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::load
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder
     */
    public function testCreateFixtureForMapperExtractContentFromRows()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $this->languageHandler
            ->expects( $this->any() )
            ->method( 'getById' )
            ->will(
                $this->returnValue(
                    new Language(
                        array(
                            'id' => 4,
                            'languageCode' => 'eng-US',
                        )
                    )
                )
            );

        $res = $gateway->load( 226, 1 );

        $res = array_merge( $res );

        $this->storeFixture(
            __DIR__ . '/../_fixtures/extract_content_from_rows.php',
            $res
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::load
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder
     */
    public function testLoadWithSingleTranslation()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $this->languageHandler
            ->expects( $this->any() )
            ->method( 'getById' )
            ->will(
                $this->returnValue(
                    new Language(
                        array(
                            'id' => 2,
                            'languageCode' => 'eng-GB',
                        )
                    )
                )
            );
        $res = $gateway->load( 226, 2, array( 'eng-GB' ) );

        $this->assertValuesInRows(
            'ezcontentobject_attribute_language_code',
            array( 'eng-GB' ),
            $res
        );
        $this->assertValuesInRows(
            'ezcontentobject_attribute_language_id',
            array( '2' ),
            $res
        );
        $this->assertEquals(
            1,
            count( $res )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::load
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder
     */
    public function testLoadNonExistentTranslation()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $res = $gateway->load( 226, 2, array( 'de-DE' ) );

        $this->assertEquals(
            0,
            count( $res )
        );
    }

    /**
     * Asserts that $columnKey in $actualRows exactly contains $expectedValues
     *
     * @param string $columnKey
     * @param string[] $expectedValues
     * @param string[][] $actualRows
     * @return void
     */
    protected function assertValuesInRows( $columnKey, array $expectedValues, array $actualRows )
    {
        $expectedValues = array_fill_keys(
            array_values( $expectedValues ),
            true
        );
        $containedValues = array();

        foreach ( $actualRows as $row )
        {
            if ( isset( $row[$columnKey] ) )
            {
                $containedValues[$row[$columnKey]] = true;
            }
        }

        $this->assertEquals(
            $expectedValues,
            $containedValues
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::getAllLocationIds
     */
    public function testGetAllLocationIds()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $this->assertEquals(
            array( 228 ),
            $gateway->getAllLocationIds( 226 )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::getFieldIdsByType
     */
    public function testGetFieldIdsByType()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $this->assertEquals(
            array(
                'ezstring' => array( 841, ),
                'ezxmltext' => array( 842, ),
                'ezimage' => array( 843, ),
                'ezkeyword' => array( 844, )
            ),
            $gateway->getFieldIdsByType( 149 )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::getFieldIdsByType
     */
    public function testGetFieldIdsByTypeWithSecondArgument()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $this->assertEquals(
            array(
                'ezstring' => array( 4001, 4002 )
            ),
            $gateway->getFieldIdsByType( 225, 2 )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::deleteRelations
     */
    public function testDeleteRelationsTo()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = array(
            'all' => $this->countContentRelations(),
            'from' => $this->countContentRelations( 149 ),
            'to' => $this->countContentRelations( null, 149 )
        );

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteRelations( 149 );

        $this->assertEquals(
        // yes, relates to itself!
            array(
                'all' => $beforeCount['all'] - 2,
                'from' => $beforeCount['from'] - 1,
                'to' => $beforeCount['to'] - 2,
            ),
            array(
                'all' => $this->countContentRelations(),
                'from' => $this->countContentRelations( 149 ),
                'to' => $this->countContentRelations( null, 149 )
            )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::deleteRelations
     */
    public function testDeleteRelationsFrom()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = array(
            'all' => $this->countContentRelations(),
            'from' => $this->countContentRelations( 75 ),
            'to' => $this->countContentRelations( null, 75 )
        );

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteRelations( 75 );

        $this->assertEquals(
            array(
                'all' => $beforeCount['all'] - 6,
                'from' => $beforeCount['from'] - 6,
                'to' => $beforeCount['to'],
            ),
            array(
                'all' => $this->countContentRelations(),
                'from' => $this->countContentRelations( 75 ),
                'to' => $this->countContentRelations( null, 75 )
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::deleteRelations
     */
    public function testDeleteRelationsWithSecondArgument()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = array(
            'all' => $this->countContentRelations(),
            'from' => $this->countContentRelations( 225 ),
            'to' => $this->countContentRelations( null, 225 )
        );

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteRelations( 225, 2 );

        $this->assertEquals(
            array(
                'all' => $beforeCount['all'] - 1,
                'from' => $beforeCount['from'] - 1,
                'to' => $beforeCount['to'],
            ),
            array(
                'all' => $this->countContentRelations(),
                'from' => $this->countContentRelations( 225 ),
                'to' => $this->countContentRelations( null, 225 )
            )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::deleteField
     */
    public function testDeleteField()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = $this->countContentFields();

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteField( 8, 1 );

        $this->assertEquals(
            $beforeCount - 1,
            $this->countContentFields()
        );

        $this->assertQueryResult(
            array(),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select( '*' )
                ->from( 'ezcontentobject_attribute' )
                ->where( 'id=8 AND version=1' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::deleteFields
     */
    public function testDeleteFields()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = array(
            'all' => $this->countContentFields(),
            'this' => $this->countContentFields( 4 ),
        );

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteFields( 4 );

        $this->assertEquals(
            array(
                'all' => $beforeCount['all'] - 2,
                'this' => 0
            ),
            array(
                'all' => $this->countContentFields(),
                'this' => $this->countContentFields( 4 ),
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::deleteFields
     */
    public function testDeleteFieldsWithSecondArgument()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = array(
            'all' => $this->countContentFields(),
            'this' => $this->countContentFields( 225 ),
        );

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteFields( 225, 2 );

        $this->assertEquals(
            array(
                'all' => $beforeCount['all'] - 2,
                'this' => $beforeCount['this'] - 2
            ),
            array(
                'all' => $this->countContentFields(),
                'this' => $this->countContentFields( 225 ),
            )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::deleteVersions
     */
    public function testDeleteVersions()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = array(
            'all' => $this->countContentVersions(),
            'this' => $this->countContentVersions( 14 )
        );

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteVersions( 14 );

        $this->assertEquals(
            array(
                'all' => $beforeCount['all'] - 2,
                'this' => 0
            ),
            array(
                'all' => $this->countContentVersions(),
                'this' => $this->countContentVersions( 14 ),
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::deleteVersions
     */
    public function testDeleteVersionsWithSecondArgument()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = array(
            'all' => $this->countContentVersions(),
            'this' => $this->countContentVersions( 225 )
        );

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteVersions( 225, 2 );

        $this->assertEquals(
            array(
                'all' => $beforeCount['all'] - 1,
                'this' => $beforeCount['this'] - 1,
            ),
            array(
                'all' => $this->countContentVersions(),
                'this' => $this->countContentVersions( 225 ),
            )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::setName
     */
    public function testSetName()
    {
        $beforeCount = array(
            'all' => $this->countContentNames(),
            'this' => $this->countContentNames( 14 )
        );

        $gateway = $this->getDatabaseGateway();
        $this->languageHandler
            ->expects( $this->once() )
            ->method( 'getByLocale' )
            ->with( 'eng-US' )
            ->will(
                $this->returnValue(
                    new Language(
                        array(
                            'id' => 2,
                            'languageCode' => 'eng-US',
                        )
                    )
                )
            );

        $gateway->setName( 14, 2, "Hello world!", 'eng-US' );

        $this->assertQueryResult(
            array( array( 'eng-US', 2, 14, 2, 'Hello world!', 'eng-US' ) ),
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select( '*' )
                ->from( 'ezcontentobject_name' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::deleteNames
     */
    public function testDeleteNames()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = array(
            'all' => $this->countContentNames(),
            'this' => $this->countContentNames( 14 )
        );

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteNames( 14 );

        $this->assertEquals(
            array(
                'all' => $beforeCount['all'] - 2,
                'this' => 0
            ),
            array(
                'all' => $this->countContentNames(),
                'this' => $this->countContentNames( 14 ),
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::deleteNames
     */
    public function testDeleteNamesWithSecondArgument()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = array(
            'all' => $this->countContentNames(),
            'this' => $this->countContentNames( 225 )
        );

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteNames( 225, 2 );

        $this->assertEquals(
            array(
                'all' => $beforeCount['all'] - 1,
                'this' => $beforeCount['this'] - 1
            ),
            array(
                'all' => $this->countContentNames(),
                'this' => $this->countContentNames( 225 ),
            )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::deleteContent
     */
    public function testDeleteContent()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = $this->countContent();

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteContent( 14 );

        $this->assertEquals(
            array(
                'all' => $beforeCount - 1,
                'this' => 0
            ),
            array(
                'all' => $this->countContent(),
                'this' => $this->countContent( 14 )
            )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::loadLatestPublishedData
     */
    public function testLoadLatestPublishedData()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $this->languageHandler
            ->expects( $this->any() )
            ->method( 'getById' )
            ->will(
                $this->returnValue(
                    new Language(
                        array(
                            'id' => 4,
                            'languageCode' => 'eng-US',
                        )
                    )
                )
            );

        $this->assertEquals(
            $gateway->loadLatestPublishedData( 10 ),
            $gateway->load( 10, 2 )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase::getLastVersionNumber
     */
    public function testGetLastVersionNumber()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $this->assertEquals(
            1,
            $gateway->getLastVersionNumber( 4 )
        );
    }

    /**
     * Counts the number of relations in the database.
     *
     * @param int $fromId
     * @param int $toId
     * @return int
     */
    protected function countContentRelations( $fromId = null, $toId = null )
    {
        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query->select( 'count(*)' )
            ->from( 'ezcontentobject_link' );

        if ( $fromId !== null )
        {
            $query->where(
                'from_contentobject_id=' . $fromId
            );
        }
        if ( $toId !== null )
        {
            $query->where(
                'to_contentobject_id=' . $toId
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Counts the number of fields
     *
     * @param int $contentId
     * @return int
     */
    protected function countContentFields( $contentId = null )
    {
        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query->select( 'count(*)' )
            ->from( 'ezcontentobject_attribute' );

        if ( $contentId !== null )
        {
            $query->where(
                'contentobject_id=' . $contentId
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Counts the number of versions
     *
     * @param int $contentId
     * @return int
     */
    protected function countContentVersions( $contentId = null )
    {
        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query->select( 'count(*)' )
            ->from( 'ezcontentobject_version' );

        if ( $contentId !== null )
        {
            $query->where(
                'contentobject_id=' . $contentId
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Counts the number of content names
     *
     * @param int $contentId
     * @return int
     */
    protected function countContentNames( $contentId = null )
    {
        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query->select( 'count(*)' )
            ->from( 'ezcontentobject_name' );

        if ( $contentId !== null )
        {
            $query->where(
                'contentobject_id=' . $contentId
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Counts the number of content objects
     *
     * @param int $contentId
     * @return int
     */
    protected function countContent( $contentId = null )
    {
        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query->select( 'count(*)' )
            ->from( 'ezcontentobject' );

        if ( $contentId !== null )
        {
            $query->where(
                'id=' . $contentId
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Stores $fixture in $file to be required as a fixture
     *
     * @param string $file
     * @param mixed $fixture
     * @return void
     */
    protected function storeFixture( $file, $fixture )
    {
        file_put_contents(
            $file,
            "<?php\n\nreturn " . var_export( $fixture, true ) . ";\n"
        );
    }

    /**
     * Returns a Field fixture
     *
     * @return Field
     */
    protected function getFieldFixture()
    {
        $field = new Field();

        $field->fieldDefinitionId = 231;
        $field->type = 'ezstring';
        $field->languageCode = 'eng-GB';
        $field->versionNo = 1;

        return $field;
    }

    /**
     * Returns a Field fixture in a different language
     *
     * @return Field
     */
    protected function getOtherLanguageFieldFixture()
    {
        $field = $this->getFieldFixture();
        $field->languageCode = 'eng-US';
        return $field;
    }

    /**
     * Returns a StorageFieldValue fixture
     *
     * @return StorageFieldValue
     */
    protected function getStorageValueFixture()
    {
        $value = new StorageFieldValue();

        $value->dataFloat = 24.42;
        $value->dataInt = 42;
        $value->dataText = 'Test text';
        $value->sortKeyInt = 23;
        $value->sortKeyString = 'Test';

        return $value;
    }

    /**
     * Returns a ready to test EzcDatabase gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase
     */
    protected function getDatabaseGateway()
    {
        if ( !isset( $this->databaseGateway ) )
        {
            $this->databaseGateway = new EzcDatabase(
                ( $dbHandler = $this->getDatabaseHandler() ),
                new EzcDatabase\QueryBuilder( $dbHandler ),
                $this->getLanguageHandler(),
                $this->getLanguageMaskGenerator()
            );
        }
        return $this->databaseGateway;
    }

    /**
     * Returns a language mask generator
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected function getLanguageMaskGenerator()
    {
        if ( !isset( $this->languageMaskGenerator ) )
        {
            $this->languageMaskGenerator = new LanguageMaskGenerator(
                $this->getLanguageLookupMock()
            );
        }
        return $this->languageMaskGenerator;
    }

    /**
     * Returns a language mask generator
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected function getLanguageHandler()
    {
        if ( !isset( $this->languageHandler ) )
        {
            $innerLanguageHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );
            $innerLanguageHandler->expects( $this->any() )
                ->method( 'loadAll' )
                ->will(
                    $this->returnValue(
                        array(
                            new Language( array(
                                'id'            => 2,
                                'languageCode'  => 'eng-GB',
                                'name'          => 'British english'
                            ) ),
                            new Language( array(
                                'id'            => 4,
                                'languageCode'  => 'eng-US',
                                'name'          => 'US english'
                            ) ),
                            new Language( array(
                                'id'            => 8,
                                'languageCode'  => 'fre-FR',
                                'name'          => 'Français franchouillard'
                            ) )
                        )
                    )
                );
            $this->languageHandler = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\CachingHandler',
                array( 'getByLocale', 'getById' ),
                array(
                    $innerLanguageHandler,
                    $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\Cache' )
                )
            );
        }
        return $this->languageHandler;
    }

    /**
     * Returns a language cache mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache
     */
    protected function getLanguageCacheMock()
    {
        $language = new Language();
        $language->id = 4;

        $languageCache = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\Cache',
            array(),
            array(),
            '',
            false
        );
        $languageCache->expects( $this->any() )
            ->method( 'getByLocale' )
            ->will( $this->returnValue( $language ) );

        return $languageCache;
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
