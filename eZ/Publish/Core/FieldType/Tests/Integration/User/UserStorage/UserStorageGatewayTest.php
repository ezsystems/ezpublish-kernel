<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\Integration\User\UserStorage;

use eZ\Publish\Core\FieldType\Tests\Integration\BaseCoreFieldTypeIntegrationTest;
use eZ\Publish\Core\Repository\Values\User\User;

/**
 * User Field Type external storage gateway tests.
 */
abstract class UserStorageGatewayTest extends BaseCoreFieldTypeIntegrationTest
{
    /**
     * @return \eZ\Publish\Core\FieldType\User\UserStorage\Gateway
     */
    abstract protected function getGateway();

    /**
     * @return array
     */
    public function providerForGetFieldData()
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
     * @param int|null $fieldId
     * @param int $userId
     * @param array $expectedUserData
     */
    public function testGetFieldData($fieldId, $userId, array $expectedUserData)
    {
        $data = $this->getGateway()->getFieldData($fieldId, $userId);
        $this->assertEquals($expectedUserData, $data);
    }
}
