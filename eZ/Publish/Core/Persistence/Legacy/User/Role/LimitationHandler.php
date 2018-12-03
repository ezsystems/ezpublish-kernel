<?php

/**
 * File containing the abstract Limitation handler.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\User\Role;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Limitation Handler.
 *
 * Takes care of Converting a Policy limitation from Legacy value to spi value accepted by API.
 */
abstract class LimitationHandler
{
    /**
     * Database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     * @deprecated Start to use DBAL $connection instead.
     */
    protected $dbHandler;

    /**
     * Creates a new criterion handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     */
    public function __construct(DatabaseHandler $dbHandler)
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * @param Policy $policy
     */
    abstract public function toLegacy(Policy $policy);

    /**
     * @param Policy $policy
     */
    abstract public function toSPI(Policy $policy);
}
