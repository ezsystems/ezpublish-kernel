<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase;
use eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase.
 */
class DoctrineDatabaseTest extends TestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase
     */
    protected $databaseGateway;

    /**
     * Inserts DB fixture.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/languages.php'
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase::insertLanguage
     */
    public function testInsertLanguage()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->insertLanguage($this->getLanguageFixture());

        $this->assertQueryResult(
            [
                [
                    'id' => '8',
                    'locale' => 'de-DE',
                    'name' => 'Deutsch (Deutschland)',
                    'disabled' => '0',
                ],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()
                ->select('id', 'locale', 'name', 'disabled')
                ->from('ezcontent_language')
                ->where('id=8')
        );
    }

    /**
     * Returns a Language fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    protected function getLanguageFixture()
    {
        $language = new Language();

        $language->languageCode = 'de-DE';
        $language->name = 'Deutsch (Deutschland)';
        $language->isEnabled = true;

        return $language;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase::updateLanguage
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase::setLanguageQueryParameters
     */
    public function testUpdateLanguage()
    {
        $gateway = $this->getDatabaseGateway();

        $language = $this->getLanguageFixture();
        $language->id = 2;

        $gateway->updateLanguage($language);

        $this->assertQueryResult(
            [
                [
                    'id' => '2',
                    'locale' => 'de-DE',
                    'name' => 'Deutsch (Deutschland)',
                    'disabled' => '0',
                ],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()
                ->select('id', 'locale', 'name', 'disabled')
                ->from('ezcontent_language')
                ->where('id=2')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase::loadLanguageListData
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase::createFindQuery
     */
    public function testLoadLanguageListData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadLanguageListData([2]);

        $this->assertEquals(
            [
                [
                    'id' => '2',
                    'locale' => 'eng-US',
                    'name' => 'English (American)',
                    'disabled' => '0',
                ],
            ],
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase::loadAllLanguagesData
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase::createFindQuery
     */
    public function testLoadAllLanguagesData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadAllLanguagesData();

        $this->assertEquals(
            [
                [
                    'id' => '2',
                    'locale' => 'eng-US',
                    'name' => 'English (American)',
                    'disabled' => '0',
                ],
                [
                    'id' => '4',
                    'locale' => 'eng-GB',
                    'name' => 'English (United Kingdom)',
                    'disabled' => '0',
                ],
            ],
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase::deleteLanguage
     */
    public function testDeleteLanguage()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->deleteLanguage(2);

        $this->assertQueryResult(
            [
                [
                    'count' => '1',
                ],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()
                ->select('COUNT( * ) AS count')
                ->from('ezcontent_language')
        );

        $this->assertQueryResult(
            [
                [
                    'count' => '0',
                ],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()
                ->select('COUNT( * ) AS count')
                ->from('ezcontent_language')
                ->where('id=2')
        );
    }

    /**
     * Return a ready to test DoctrineDatabase gateway.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getDatabaseGateway(): DoctrineDatabase
    {
        if (!isset($this->databaseGateway)) {
            $this->databaseGateway = new DoctrineDatabase(
                $this->getDatabaseConnection()
            );
        }

        return $this->databaseGateway;
    }
}
