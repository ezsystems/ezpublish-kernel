<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\Integration\User\UserStorage;

use eZ\Publish\Core\FieldType\Tests\Integration\BaseCoreFieldTypeIntegrationTest;

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
                'passwordHash' => '4e6f6184135228ccd45f8233d72a0363',
                'passwordHashType' => '2',
                'enabled' => true,
                'maxLogin' => 1000,
            ],
            14 => [
                'hasStoredLogin' => true,
                'contentId' => 14,
                'login' => 'admin',
                'email' => 'spam@ez.no',
                'passwordHash' => 'c78e3b0f3d9244ed8c6d1c29464bdff9',
                'passwordHashType' => '2',
                'enabled' => true,
                'maxLogin' => 10,
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
