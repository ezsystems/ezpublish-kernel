<?php

/**
 * File containing the BinaryFileStorage Gateway.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\BinaryFile\BinaryFileStorage\Gateway;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\FieldType\BinaryBase\BinaryBaseStorage\Gateway\LegacyStorage as BaseLegacyStorage;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Database\InsertQuery;

/**
 * @deprecated since 6.11. Use {@see \eZ\Publish\Core\FieldType\BinaryFile\BinaryFileStorage\Gateway\DoctrineStorage} instead.
 */
class LegacyStorage extends BaseLegacyStorage
{
    /**
     * Returns the table name to store data in.
     *
     * @return string
     */
    protected function getStorageTable()
    {
        return 'ezbinaryfile';
    }

    /**
     * Returns a column to property mapping for the storage table.
     *
     * @return array
     */
    protected function getPropertyMapping()
    {
        $propertyMap = parent::getPropertyMapping();
        $propertyMap['download_count'] = [
            'name' => 'downloadCount',
            'cast' => 'intval',
        ];

        return $propertyMap;
    }

    /**
     * Set columns to be fetched from the database.
     *
     * This method is intended to be overwritten by derived classes in order to
     * add additional columns to be fetched from the database. Please do not
     * forget to call the parent when overwriting this method.
     *
     * @param eZ\Publish\Core\Persistence\Database\SelectQuery $selectQuery
     * @param int $fieldId
     * @param int $versionNo
     */
    protected function setFetchColumns(SelectQuery $selectQuery, $fieldId, $versionNo)
    {
        $connection = $this->getConnection();

        parent::setFetchColumns($selectQuery, $fieldId, $versionNo);
        $selectQuery->select(
            $connection->quoteColumn('download_count')
        );
    }

    /**
     * Sets the required insert columns to $selectQuery.
     *
     * This method is intended to be overwritten by derived classes in order to
     * add additional columns to be set in the database. Please do not forget
     * to call the parent when overwriting this method.
     *
     * @param \ezcQueryInsert $insertQuery
     * @param VersionInfo $versionInfo
     * @param Field $field
     */
    protected function setInsertColumns(InsertQuery $insertQuery, VersionInfo $versionInfo, Field $field)
    {
        $connection = $this->getConnection();

        parent::setInsertColumns($insertQuery, $versionInfo, $field);
        $insertQuery->set(
            $connection->quoteColumn('download_count'),
            $insertQuery->bindValue($field->value->externalData['downloadCount'], null, \PDO::PARAM_INT)
        );
    }
}
