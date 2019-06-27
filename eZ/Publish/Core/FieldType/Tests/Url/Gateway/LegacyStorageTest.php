<?php

/**
 * File containing the LegacyStorageTest for Url FieldType.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\Url\Gateway;

use eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway\LegacyStorage;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;

/**
 * Tests the Url LegacyStorage gateway.
 */
class LegacyStorageTest extends TestCase
{
    public function testGetIdUrlMap()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urls.php');

        $gateway = $this->getStorageGateway();

        $this->assertEquals(
            [
                23 => '/content/view/sitemap/2',
                24 => '/content/view/tagcloud/2',
            ],
            $gateway->getIdUrlMap(
                [23, 24, 'fake']
            )
        );
    }

    public function testGetUrlIdMap()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urls.php');

        $gateway = $this->getStorageGateway();

        $this->assertEquals(
            [
                '/content/view/sitemap/2' => 23,
                '/content/view/tagcloud/2' => 24,
            ],
            $gateway->getUrlIdMap(
                [
                    '/content/view/sitemap/2',
                    '/content/view/tagcloud/2',
                    'fake',
                ]
            )
        );
    }

    public function testInsertUrl()
    {
        $gateway = $this->getStorageGateway();

        $url = 'one/two/three';
        $time = time();
        $id = $gateway->insertUrl($url);

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query
            ->select('*')
            ->from('ezurl')
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('id'),
                    $query->bindValue($id)
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $expected = [
            [
                'id' => $id,
                'is_valid' => '1',
                'last_checked' => '0',
                'original_url_md5' => md5($url),
                'url' => $url,
            ],
        ];

        $this->assertGreaterThanOrEqual($time, $result[0]['created']);
        $this->assertGreaterThanOrEqual($time, $result[0]['modified']);

        unset($result[0]['created']);
        unset($result[0]['modified']);

        $this->assertEquals($expected, $result);
    }

    public function testLinkUrl()
    {
        $gateway = $this->getStorageGateway();

        $urlId = 12;
        $fieldId = 10;
        $versionNo = 1;
        $gateway->linkUrl($urlId, $fieldId, $versionNo);

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query->select(
            '*'
        )->from(
            'ezurl_object_link'
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn('url_id'),
                $query->bindValue($urlId)
            )
        );
        $statement = $query->prepare();
        $statement->execute();

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $expected = [
            [
                'contentobject_attribute_id' => $fieldId,
                'contentobject_attribute_version' => $versionNo,
                'url_id' => $urlId,
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testUnlinkUrl()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urls.php');

        $gateway = $this->getStorageGateway();

        $fieldId = 42;
        $versionNo = 5;
        $gateway->unlinkUrl($fieldId, $versionNo);

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query->select('*')->from('ezurl_object_link');
        $statement = $query->prepare();
        $statement->execute();

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $expected = [
            [
                'contentobject_attribute_id' => 43,
                'contentobject_attribute_version' => 6,
                'url_id' => 24,
            ],
        ];

        $this->assertEquals($expected, $result);

        // Check that orphaned URLs are correctly removed
        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query->select('*')->from('ezurl');
        $statement = $query->prepare();
        $statement->execute();

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $expected = [
            [
                'created' => '1343140541',
                'id' => '24',
                'is_valid' => '1',
                'last_checked' => '0',
                'modified' => '1343140541',
                'original_url_md5' => 'c86bcb109d8e70f9db65c803baafd550',
                'url' => '/content/view/tagcloud/2',
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    /** @var \eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway\LegacyStorage */
    protected $storageGateway;

    /**
     * Returns a ready to test LegacyStorage gateway.
     *
     * @return \eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway\LegacyStorage
     */
    protected function getStorageGateway()
    {
        if (!isset($this->storageGateway)) {
            $this->storageGateway = new LegacyStorage($this->getDatabaseHandler());
        }

        return $this->storageGateway;
    }
}
