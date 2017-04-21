<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Database\Platform;

use Doctrine\DBAL\Platforms\MySqlPlatform as BaseMySqlPlatform;
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\DBAL\Schema\Index;

/**
 * MySQL Database Platform for Doctrine SchemaManager.
 */
class MySQLPlatform extends BaseMySqlPlatform
{
    /**
     * {@inheritdoc}
     */
    public function getIndexDeclarationSQL($name, Index $index)
    {
        if (!$index->hasOption('length')) {
            return parent::getIndexDeclarationSQL($name, $index);
        }

        $columns = $index->getQuotedColumns($this);
        $name = new Identifier($name);

        if (count($columns) === 0) {
            throw new \InvalidArgumentException("Incomplete definition. 'columns' required.");
        }

        return $this->getCreateIndexSQLFlags($index) . 'INDEX ' . $name->getQuotedName($this) . ' ('
            . $this->getIndexFieldDeclarationListSQL($columns, $index->getOption('length'))
            . ')' . $this->getPartialIndexSQL($index);
    }

    /**
     * Obtains DBMS specific SQL code portion needed to set an index
     * declaration to be used in statements like CREATE TABLE.
     *
     * @param array $fields
     * @param array $length MySQL index field lengths configuration
     *
     * @return string
     */
    public function getIndexFieldDeclarationListSQL(array $fields, array $length = [])
    {
        $columns = [];

        foreach ($fields as $field => $definition) {
            if (isset($length[$definition])) {
                $definition .= '(' . intval($length[$definition]) . ')';
            }
            $columns[] = $definition;
        }

        return implode(', ', $columns);
    }
}
