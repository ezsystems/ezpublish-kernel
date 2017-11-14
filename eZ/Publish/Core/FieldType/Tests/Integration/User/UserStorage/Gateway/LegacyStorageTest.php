<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\Integration\User\UserStorage\Gateway;

use eZ\Publish\Core\FieldType\Tests\Integration\User\UserStorage\UserStorageGatewayTest;
use eZ\Publish\Core\FieldType\User\UserStorage\Gateway\LegacyStorage;

class LegacyStorageTest extends UserStorageGatewayTest
{
    /**
     * @return \eZ\Publish\Core\FieldType\User\UserStorage\Gateway
     */
    protected function getGateway()
    {
        $dbHandler = $this->getDatabaseHandler();

        return new LegacyStorage($dbHandler);
    }
}
