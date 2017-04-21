<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Database\Platform;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform as BasePostgreSqlPlatform;
use Doctrine\DBAL\Schema\Sequence;

/**
 * PostgreSQL Database Platform for Doctrine SchemaManager.
 */
class PostgreSQLPlatform extends BasePostgreSqlPlatform
{
    /**
     * {@inheritdoc}
     */
    public function getAlterSequenceSQL(Sequence $sequence)
    {
        return parent::getAlterSequenceSQL($sequence)
            . ' RESTART WITH ' . $sequence->getInitialValue();
    }
}
