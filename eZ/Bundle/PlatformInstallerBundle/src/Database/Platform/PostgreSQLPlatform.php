<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Database\Platform;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform as BasePostgreSqlPlatform;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Sequence;
use Doctrine\DBAL\Schema\Table;

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

    /**
     * {@inheritdoc}
     */
    public function getCreateIndexSQL(Index $index, $table)
    {
        if (!$index->hasOption('wrap_in')) {
            return parent::getCreateIndexSQL($index, $table);
        }

        if ($table instanceof Table) {
            $table = $table->getQuotedName($this);
        }
        $name = $index->getQuotedName($this);
        $columns = $index->getQuotedColumns($this);

        if (count($columns) == 0) {
            throw new \InvalidArgumentException("Incomplete definition. 'columns' required.");
        }

        if ($index->isPrimary()) {
            return $this->getCreatePrimaryKeySQL($index, $table);
        }

        $query = 'CREATE ' . $this->getCreateIndexSQLFlags($index) . 'INDEX ' . $name . ' ON ' . $table;
        $query .= ' (' . $this->getIndexFieldDeclarationListSQL($columns, $index->getOption('wrap_in')) . ')' . $this->getPartialIndexSQL($index);

        return $query;
    }

    /**
     * Obtains DBMS specific SQL code portion needed to set an index
     * declaration to be used in statements like CREATE TABLE.
     *
     * @param array $fields
     * @param array $wrapIn expression based index wrapper functions for columns mapping
     *
     * @return string
     */
    public function getIndexFieldDeclarationListSQL(array $fields, array $wrapIn = [])
    {
        $columns = [];
        foreach ($fields as $field => $definition) {
            $columns[] = empty($wrapIn[$definition]) ? $definition : "{$wrapIn[$definition]}($definition)";
        }

        return implode(', ', $columns);
    }
}
