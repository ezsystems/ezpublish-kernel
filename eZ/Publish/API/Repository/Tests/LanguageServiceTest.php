<?php
/**
 * File containing the LanguageServiceTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Test case for operations in the LanguageService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\LanguageService
 * @group integration
 * @group language
 */
class LanguageServiceTest extends BaseTest
{
    /**
     * Test for the newLanguageCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::newLanguageCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentLanguageService
     */
    public function testNewLanguageCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreate = $languageService->newLanguageCreateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\LanguageCreateStruct',
            $languageCreate
        );
    }

    /**
     * Test for the createLanguage() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testNewLanguageCreateStruct
     */
    public function testCreateLanguage()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreate = $languageService->newLanguageCreateStruct();
        $languageCreate->enabled = true;
        $languageCreate->name = 'English (New Zealand)';
        $languageCreate->languageCode = 'eng-NZ';

        $language = $languageService->createLanguage( $languageCreate );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Language',
            $language
        );

        return $language;
    }

    /**
     * Test for the createLanguage() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testCreateLanguageSetsIdPropertyOnReturnedLanguage( $language )
    {
        $this->assertNotNull( $language->id );
    }

    /**
     * Test for the createLanguage() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testCreateLanguageSetsExpectedProperties( $language )
    {
        $this->assertEquals(
            array(
                true,
                'English (New Zealand)',
                'eng-NZ'
            ),
            array(
                $language->enabled,
                $language->name,
                $language->languageCode
            )
        );
    }

    /**
     * Test for the createLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testCreateLanguageThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreate = $languageService->newLanguageCreateStruct();
        $languageCreate->enabled = true;
        $languageCreate->name = 'Norwegian';
        $languageCreate->languageCode = 'nor-NO';

        $languageService->createLanguage( $languageCreate );

        // This call should fail with an InvalidArgumentException, because
        // the language code "nor-NO" already exists.
        $languageService->createLanguage( $languageCreate );
        /* END: Use Case */
    }

    /**
     * Test for the loadLanguageById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::loadLanguageById()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testLoadLanguageById()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreate = $languageService->newLanguageCreateStruct();
        $languageCreate->enabled = false;
        $languageCreate->name = 'English';
        $languageCreate->languageCode = 'eng-NZ';

        $languageId = $languageService->createLanguage( $languageCreate )->id;

        $language = $languageService->loadLanguageById( $languageId );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Language',
            $language
        );
    }

    /**
     * Test for the loadLanguageById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::loadLanguageById()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testLoadLanguageById
     */
    public function testLoadLanguageByIdThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistentLanguageId = $this->generateId( 'language', 2342 );
        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        // This call should fail with a "NotFoundException"
        $languageService->loadLanguageById( $nonExistentLanguageId );
        /* END: Use Case */
    }

    /**
     * Test for the updateLanguageName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::updateLanguageName()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testLoadLanguageById
     */
    public function testUpdateLanguageName()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreate = $languageService->newLanguageCreateStruct();
        $languageCreate->enabled = false;
        $languageCreate->name = 'English';
        $languageCreate->languageCode = 'eng-NZ';

        $languageId = $languageService->createLanguage( $languageCreate )->id;

        $language = $languageService->loadLanguageById( $languageId );

        $updatedLanguage = $languageService->updateLanguageName(
            $language,
            'New language name.'
        );
        /* END: Use Case */

        // Verify that the service returns an updated language instance.
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Language',
            $updatedLanguage
        );

        // Verify that the service also persists the changes
        $updatedLanguage = $languageService->loadLanguageById( $languageId );

        $this->assertEquals( 'New language name.', $updatedLanguage->name );
    }

    /**
     * Test for the enableLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::enableLanguage()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testLoadLanguageById
     */
    public function testEnableLanguage()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreate = $languageService->newLanguageCreateStruct();
        $languageCreate->enabled = false;
        $languageCreate->name = 'English';
        $languageCreate->languageCode = 'eng-NZ';

        $language = $languageService->createLanguage( $languageCreate );

        // Now lets enable the newly created language
        $languageService->enableLanguage( $language );

        $enabledLanguage = $languageService->loadLanguageById( $language->id );
        /* END: Use Case */

        $this->assertTrue( $enabledLanguage->enabled );
    }

    /**
     * Test for the disableLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::disableLanguage()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testLoadLanguageById
     */
    public function testDisableLanguage()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreate = $languageService->newLanguageCreateStruct();
        $languageCreate->enabled = true;
        $languageCreate->name = 'English';
        $languageCreate->languageCode = 'eng-NZ';

        $language = $languageService->createLanguage( $languageCreate );

        // Now lets disable the newly created language
        $languageService->disableLanguage( $language );

        $enabledLanguage = $languageService->loadLanguageById( $language->id );
        /* END: Use Case */

        $this->assertFalse( $enabledLanguage->enabled );
    }

    /**
     * Test for the loadLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::loadLanguage()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testLoadLanguage()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreate = $languageService->newLanguageCreateStruct();
        $languageCreate->enabled = true;
        $languageCreate->name = 'English';
        $languageCreate->languageCode = 'eng-NZ';

        $languageId = $languageService->createLanguage( $languageCreate )->id;

        // Now load the newly created language by it's language code
        $language = $languageService->loadLanguage( 'eng-NZ' );
        /* END: Use Case */

        $this->assertEquals( $languageId, $language->id );
    }

    /**
     * Test for the loadLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::loadLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testLoadLanguage
     */
    public function testLoadLanguageThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        // This call should fail with an exception
        $languageService->loadLanguage( 'fre-FR' );
        /* END: Use Case */
    }

    /**
     * Test for the loadLanguages() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::loadLanguages()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     * @todo Enhance to check for language codes and properties?
     */
    public function testLoadLanguages()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        // Create some languages
        $languageCreateEnglish = $languageService->newLanguageCreateStruct();
        $languageCreateEnglish->enabled = false;
        $languageCreateEnglish->name = 'English';
        $languageCreateEnglish->languageCode = 'eng-NZ';

        $languageCreateFrench = $languageService->newLanguageCreateStruct();
        $languageCreateFrench->enabled = false;
        $languageCreateFrench->name = 'French';
        $languageCreateFrench->languageCode = 'fre-FR';

        $languageService->createLanguage( $languageCreateEnglish );
        $languageService->createLanguage( $languageCreateFrench );

        $languages = $languageService->loadLanguages();
        foreach ( $languages as $language )
        {
            // Operate on each language
        }
        /* END: Use Case */

        // eng-US, eng-GB, ger-DE + 2 newly created
        $this->assertEquals( 5, count( $languages ) );
    }

    /**
     * Test for the loadLanguages() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::loadLanguages()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function loadLanguagesReturnsAnEmptyArrayByDefault()
    {
        $repository = $this->getRepository();

        $languageService = $repository->getContentLanguageService();

        $this->assertSame( array(), $languageService->loadLanguages() );
    }

    /**
     * Test for the deleteLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::deleteLanguage()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testLoadLanguages
     */
    public function testDeleteLanguage()
    {
        $repository = $this->getRepository();
        $languageService = $repository->getContentLanguageService();

        $beforeCount = count( $languageService->loadLanguages() );

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreateEnglish = $languageService->newLanguageCreateStruct();
        $languageCreateEnglish->enabled = false;
        $languageCreateEnglish->name = 'English';
        $languageCreateEnglish->languageCode = 'eng-NZ';

        $language = $languageService->createLanguage( $languageCreateEnglish );

        // Delete the newly created language
        $languageService->deleteLanguage( $language );
        /* END: Use Case */

        // +1 -1
        $this->assertEquals( $beforeCount, count( $languageService->loadLanguages() ) );
    }

    /**
     * Test for the deleteLanguage() method.
     *
     * NOTE: This test has a dependency against several methods in the content
     * service, but because there is no topological sort for test dependencies
     * we cannot declare them here.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::deleteLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testDeleteLanguage
     * @depend(s) eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testDeleteLanguageThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId( 'group', 13 );
        /* BEGIN: Use Case */
        // $editorsGroupId is the ID of the "Editors" user group in an eZ
        // Publish demo installation

        $languageService = $repository->getContentLanguageService();

        $languageCreateEnglish = $languageService->newLanguageCreateStruct();
        $languageCreateEnglish->enabled = true;
        $languageCreateEnglish->name = 'English';
        $languageCreateEnglish->languageCode = 'eng-NZ';

        $language = $languageService->createLanguage( $languageCreateEnglish );

        $contentService = $repository->getContentService();

        // Get metadata update struct and set new language as main language.
        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();
        $metadataUpdate->mainLanguageCode = 'eng-NZ';

        // Update content object
        $contentService->updateContentMetadata(
            $contentService->loadContentInfo( $editorsGroupId ),
            $metadataUpdate
        );

        // This call will fail with an "InvalidArgumentException", because the
        // new language is used by a content object.
        $languageService->deleteLanguage( $language );
        /* END: Use Case */
    }

    /**
     * Test for the getDefaultLanguageCode() method.
     *
     * @see \eZ\Publish\API\Repository\LanguageService::getDefaultLanguageCode()
     *
     * @return void
     */
    public function testGetDefaultLanguageCode()
    {
        $repository = $this->getRepository();
        $languageService = $repository->getContentLanguageService();

        $this->assertRegExp(
            '(^[a-z]{3}\-[A-Z]{2}$)',
            $languageService->getDefaultLanguageCode()
        );
    }

    /**
     * Helper method that creates a new language test fixture in the
     * API implementation under test.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    private function createLanguage()
    {
        $repository = $this->getRepository();

        $languageService = $repository->getContentLanguageService();
        $languageCreate = $languageService->newLanguageCreateStruct();

        $languageCreate->enabled = false;
        $languageCreate->name = 'English';
        $languageCreate->languageCode = 'eng-US';

        return $languageService->createLanguage( $languageCreate );
    }

    /**
     * Test for the createLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testRollback
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testCreateLanguageInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Get create struct and set properties
            $languageCreate = $languageService->newLanguageCreateStruct();
            $languageCreate->enabled = true;
            $languageCreate->name = 'English (New Zealand)';
            $languageCreate->languageCode = 'eng-NZ';

            // Create new language
            $languageService->createLanguage( $languageCreate );
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        try
        {
            // This call will fail with a "NotFoundException"
            $languageService->loadLanguage( 'eng-NZ' );
        }
        catch ( NotFoundException $e )
        {
            // Expected execution path
        }
        /* END: Use Case */

        $this->assertTrue( isset( $e ), 'Can still load language after rollback' );
    }

    /**
     * Test for the createLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testCommit
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testCreateLanguageInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Get create struct and set properties
            $languageCreate = $languageService->newLanguageCreateStruct();
            $languageCreate->enabled = true;
            $languageCreate->name = 'English (New Zealand)';
            $languageCreate->languageCode = 'eng-NZ';

            // Create new language
            $languageService->createLanguage( $languageCreate );

            // Commit all changes
            $repository->commit();
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load new language
        $language = $languageService->loadLanguage( 'eng-NZ' );
        /* END: Use Case */

        $this->assertEquals( 'eng-NZ', $language->languageCode );
    }

    /**
     * Test for the updateLanguageName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::updateLanguageName()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testRollback
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testUpdateLanguageName
     */
    public function testUpdateLanguageNameInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Load an existing language
            $language = $languageService->loadLanguage( 'eng-US' );

            // Update the language name
            $languageService->updateLanguageName( $language, 'My English' );
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        // Load updated version, name will still be "English (American)"
        $updatedLanguage = $languageService->loadLanguage( 'eng-US' );
        /* END: Use Case */

        $this->assertEquals( 'English (American)', $updatedLanguage->name );
    }

    /**
     * Test for the updateLanguageName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::updateLanguageName()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testCommit
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testUpdateLanguageName
     */
    public function testUpdateLanguageNameInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Load an existing language
            $language = $languageService->loadLanguage( 'eng-US' );

            // Update the language name
            $languageService->updateLanguageName( $language, 'My English' );

            // Commit all changes
            $repository->commit();
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load updated version, name will be "My English"
        $updatedLanguage = $languageService->loadLanguage( 'eng-US' );
        /* END: Use Case */

        $this->assertEquals( 'My English', $updatedLanguage->name );
    }
}
