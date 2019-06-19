<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlWildcard\Gateway\DoctrineDatabaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlWildcard\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\DoctrineDatabase;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\DoctrineDatabase.
 */
class DoctrineDatabaseTest extends TestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\DoctrineDatabase
     */
    protected $gateway;

    protected $fixtureData = [
        0 => [
            'id' => '1',
            'source_url' => 'developer/*',
            'destination_url' => 'dev/{1}',
            'type' => '2',
        ],
        1 => [
            'id' => '2',
            'source_url' => 'repository/*',
            'destination_url' => 'repo/{1}',
            'type' => '2',
        ],
        2 => [
            'id' => '3',
            'source_url' => 'information/*',
            'destination_url' => 'info/{1}',
            'type' => '2',
        ],
    ];

    /**
     * Test for the __construct() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\DoctrineDatabase::__construct
     */
    public function testConstructor()
    {
        $dbHandler = $this->getDatabaseHandler();
        $gateway = $this->getGateway();

        $this->assertAttributeSame(
            $dbHandler,
            'dbHandler',
            $gateway
        );
    }

    /**
     * Test for the loadUrlWildcardData() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\DoctrineDatabase::loadUrlWildcardData
     */
    public function testLoadUrlWildcardData()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlwildcards.php');
        $gateway = $this->getGateway();

        $row = $gateway->loadUrlWildcardData(1);

        self::assertEquals(
            $this->fixtureData[0],
            $row
        );
    }

    /**
     * Test for the loadUrlWildcardsData() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\DoctrineDatabase::loadUrlWildcardsData
     */
    public function testLoadUrlWildcardsData()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlwildcards.php');
        $gateway = $this->getGateway();

        $rows = $gateway->loadUrlWildcardsData();

        self::assertEquals(
            $this->fixtureData,
            $rows
        );
    }

    /**
     * Test for the loadUrlWildcardsData() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\DoctrineDatabase::loadUrlWildcardsData
     */
    public function testLoadUrlWildcardsDataWithOffset()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlwildcards.php');
        $gateway = $this->getGateway();

        $row = $gateway->loadUrlWildcardsData(1);

        self::assertEquals(
            [
                0 => $this->fixtureData[1],
                1 => $this->fixtureData[2],
            ],
            $row
        );
    }

    /**
     * Test for the loadUrlWildcardsData() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\DoctrineDatabase::loadUrlWildcardsData
     */
    public function testLoadUrlWildcardsDataWithOffsetAndLimit()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlwildcards.php');
        $gateway = $this->getGateway();

        $row = $gateway->loadUrlWildcardsData(1, 1);

        self::assertEquals(
            [
                0 => $this->fixtureData[1],
            ],
            $row
        );
    }

    /**
     * Test for the insertUrlWildcard() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\DoctrineDatabase::insertUrlWildcard
     * @depends testLoadUrlWildcardData
     */
    public function testInsertUrlWildcard()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlwildcards.php');
        $gateway = $this->getGateway();

        $id = $gateway->insertUrlWildcard(
            new UrlWildcard(
                [
                    'sourceUrl' => '/contact-information/*',
                    'destinationUrl' => '/contact/{1}',
                    'forward' => true,
                ]
            )
        );

        self::assertEquals(
            [
                'id' => $id,
                'source_url' => 'contact-information/*',
                'destination_url' => 'contact/{1}',
                'type' => '1',
            ],
            $gateway->loadUrlWildcardData($id)
        );
    }

    /**
     * Test for the deleteUrlWildcard() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\DoctrineDatabase::deleteUrlWildcard
     * @depends testLoadUrlWildcardData
     */
    public function testDeleteUrlWildcard()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlwildcards.php');
        $gateway = $this->getGateway();

        $gateway->deleteUrlWildcard(1);

        self::assertEmpty($gateway->loadUrlWildcardData(1));
    }

    /**
     * Returns the DoctrineDatabase gateway to test.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\DoctrineDatabase
     */
    protected function getGateway()
    {
        if (!isset($this->gateway)) {
            $this->gateway = new DoctrineDatabase($this->getDatabaseHandler());
        }

        return $this->gateway;
    }
}
