<?php

/**
 * File containing the LegacyStorageTest for RichText FieldType.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\RichText\Gateway;

use eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway\LegacyStorage;
use eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway\LegacyStorage as UrlStorage;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;

/**
 * Tests the RichText LegacyStorage.
 */
class LegacyStorageTest extends TestCase
{
    public function testSetConnection()
    {
        $gateway = $this->getStorageGateway();

        $gateway->setConnection($this->getMock('eZ\\Publish\\Core\\Persistence\\Database\\DatabaseHandler'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetConnectionThrowsRuntimeException()
    {
        $gateway = $this->getStorageGateway();

        $gateway->setConnection(new \DateTime());
    }

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
     * @var \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway\LegacyStorage
     */
    protected $storageGateway;

    /**
     * Returns a ready to test LegacyStorage gateway.
     *
     * @return \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway\LegacyStorage
     */
    protected function getStorageGateway()
    {
        if (!isset($this->storageGateway)) {
            $this->storageGateway = new LegacyStorage(
                new UrlStorage()
            );
            $this->storageGateway->setConnection($this->getDatabaseHandler());
        }

        return $this->storageGateway;
    }
}
