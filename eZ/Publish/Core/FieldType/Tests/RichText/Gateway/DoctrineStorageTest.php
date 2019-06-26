<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\RichText\Gateway;

use eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway\DoctrineStorage;
use eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway\DoctrineStorage as UrlStorageDoctrineGateway;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;

/**
 * Tests the RichText DoctrineStorage.
 */
class DoctrineStorageTest extends TestCase
{
    /** @var \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway\DoctrineStorage */
    protected $storageGateway;

    public function testGetContentIds()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/contentobjects.php');

        $gateway = $this->getStorageGateway();

        $this->assertEquals(
            [
                'f5c88a2209584891056f987fd965b0ba' => 4,
                'faaeb9be3bd98ed09f606fc16d144eca' => 10,
            ],
            $gateway->getContentIds(
                [
                    'f5c88a2209584891056f987fd965b0ba',
                    'faaeb9be3bd98ed09f606fc16d144eca',
                    'fake',
                ]
            )
        );
    }

    /**
     * Return a ready to test DoctrineStorage gateway.
     *
     * @return \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway\DoctrineStorage
     */
    protected function getStorageGateway()
    {
        if (!isset($this->storageGateway)) {
            $connection = $this->getDatabaseHandler()->getConnection();
            $urlGateway = new UrlStorageDoctrineGateway($connection);
            $this->storageGateway = new DoctrineStorage($urlGateway, $connection);
        }

        return $this->storageGateway;
    }
}
