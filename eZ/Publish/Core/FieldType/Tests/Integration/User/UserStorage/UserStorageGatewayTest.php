<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\Integration\User\UserStorage;

use eZ\Publish\Core\FieldType\Tests\Integration\BaseCoreFieldTypeIntegrationTest;
use eZ\Publish\Core\FieldType\User\UserStorage\Gateway;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\SPI\Tests\Persistence\FixtureImporter;
use eZ\Publish\SPI\Tests\Persistence\YamlFixture;

/**
 * User Field Type external storage gateway tests.
 */
abstract class UserStorageGatewayTest extends BaseCoreFieldTypeIntegrationTest
{
    abstract protected function getGateway(): Gateway;

    public function providerForGetFieldData(): array
    {
        $expectedUserData = [
            10 => [
                'hasStoredLogin' => true,
                'contentId' => 10,
                'login' => 'anonymous',
                'email' => 'nospam@ez.no',
                'passwordHash' => '$2y$10$35gOSQs6JK4u4whyERaeUuVeQBi2TUBIZIfP7HEj7sfz.MxvTuOeC',
                'passwordHashType' => User::PASSWORD_HASH_PHP_DEFAULT,
                'enabled' => true,
                'maxLogin' => 1000,
                'passwordUpdatedAt' => null,
            ],
            14 => [
                'hasStoredLogin' => true,
                'contentId' => 14,
                'login' => 'admin',
                'email' => 'spam@ez.no',
                'passwordHash' => '$2y$10$FDn9NPwzhq85cLLxfD5Wu.L3SL3Z/LNCvhkltJUV0wcJj7ciJg2oy',
                'passwordHashType' => User::PASSWORD_HASH_PHP_DEFAULT,
                'enabled' => true,
                'maxLogin' => 10,
                'passwordUpdatedAt' => null,
            ],
        ];

        return [
            [null, 10, $expectedUserData[10]],
            [21, null, $expectedUserData[10]],
            [null, 14, $expectedUserData[14]],
            [28, null, $expectedUserData[14]],
        ];
    }

    /**
     * @dataProvider providerForGetFieldData
     */
    public function testGetFieldData(?int $fieldId, ?int $userId, array $expectedUserData): void
    {
        $data = $this->getGateway()->getFieldData($fieldId, $userId);
        self::assertEquals($expectedUserData, $data);
    }

    /**
     * @dataProvider getDataForTestCountUsersWithUnsupportedHashType
     */
    public function testCountUsersWithUnsupportedHashType(
        int $expectedCount,
        ?string $fixtureFilePath
    ): void {
        if (null !== $fixtureFilePath) {
            $importer = new FixtureImporter($this->getDatabaseConnection());
            $importer->import(new YamlFixture($fixtureFilePath));
        }

        $actualCount = $this->getGateway()->countUsersWithUnsupportedHashType();
        self::assertEquals($expectedCount, $actualCount);
    }

    public function getDataForTestCountUsersWithUnsupportedHashType(): iterable
    {
        yield 'no unsupported hashes' => [
            0,
            null,
        ];

        yield 'with unsupported hash' => [
            1,
            __DIR__ . '/_fixtures/unsupported_hash.yaml',
        ];
    }
}
