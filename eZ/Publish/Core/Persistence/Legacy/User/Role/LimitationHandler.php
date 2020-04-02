<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\User\Role;

use Doctrine\DBAL\Connection;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Takes care of Converting a Policy limitation from Legacy value to spi value accepted by API.
 */
abstract class LimitationHandler
{
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    abstract public function toLegacy(Policy $policy): void;

    abstract public function toSPI(Policy $policy): void;
}
