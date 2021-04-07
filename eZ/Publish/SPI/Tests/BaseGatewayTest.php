<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Tests;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Tests\SetupFactory\Legacy;

abstract class BaseGatewayTest extends BaseTest
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    protected function setUp(): void
    {
        $this->repository = (new Legacy())->getRepository(true);
    }
}
