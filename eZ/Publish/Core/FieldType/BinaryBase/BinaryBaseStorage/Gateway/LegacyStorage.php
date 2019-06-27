<?php

/**
 * File containing the BinaryBaseStorage Gateway.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\BinaryBase\BinaryBaseStorage\Gateway;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\FieldType\BinaryBase\BinaryBaseStorage\Gateway;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Database\InsertQuery;

/**
 * @deprecated since 6.11. Use {@see \eZ\Publish\Core\FieldType\BinaryBase\BinaryBaseStorage\Gateway\DoctrineStorage} instead.
 */
abstract class LegacyStorage extends Gateway
{
    /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler */
    protected $dbHandler;

    public function __construct(DatabaseHandler $dbHandler)
    {
        @trigger_error(
            sprintf('%s is deprecated, use %s instead', self::class, DoctrineStorage::class),
            E_USER_DEPRECATED
        );
        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the table name to store data in.
     *
     * @return string
     */
    abstract protected function getStorageTable();

    /**
     * Returns a column to property mapping for the storage table.
     *
     * @return array
     */
    protected function getPropertyMapping()
    {
        return [
            'filename' => [
                'name' => 'id',
                'cast' => 'strval',
            ],
            'mime_type' => [
                'name' => 'mimeType',
                'cast' => 'strval',
            ],
            'original_filename' => [
                'name' => 'fileName',
                'cast' => 'strval',
            ],
        ];
    }

    /**
     * Set columns to be fetched from the database.
     *
     * This method is intended to be overwritten by derived classes in order to
     * add additional columns to be fetched from the database. Please do not
     * forget to call the parent when overwriting this method.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $selectQuery
     * @param int $fieldId
     * @param int $versionNo
     */
    protected function setFetchColumns(SelectQuery $selectQuery, $fieldId, $versionNo)
    {
        $connection = $this->getConnection();

        $selectQuery->select(
            $connection->quoteColumn('filename'),
            $connection->quoteColumn('mime_type'),
            $connection->quoteColumn('original_filename')
        );
    }

    /**
     * Sets the required insert columns to $selectQuery.
     *
     * This method is intended to be overwritten by derived classes in order to
     * add additional columns to be set in the database. Please do not forget
     * to call the parent when overwriting this method.
     *
     * @param \eZ\Publish\Core\Persistence\Database\InsertQuery $insertQuery
     * @param VersionInfo $versionInfo
     * @param Field $field
     */
    protected function setInsertColumns(InsertQuery $insertQuery, VersionInfo $versionInfo, Field $field)
    {
        $connection = $this->getConnection();

        $insertQuery->set(
            $connection->quoteColumn('contentobject_attribute_id'),
            $insertQuery->bindValue($field->id, null, \PDO::PARAM_INT)
        )->set(
            $connection->quoteColumn('filename'),
            $insertQuery->bindValue(
                $this->removeMimeFromPath($field->value->externalData['id'])
            )
        )->set(
            $connection->quoteColumn('mime_type'),
            $insertQuery->bindValue($field->value->externalData['mimeType'])
        )->set(
            $connection->quoteColumn('original_filename'),
            $insertQuery->bindValue($field->value->externalData['fileName'])
        )->set(
            $connection->quoteColumn('version'),
            $insertQuery->bindValue($versionInfo->versionNo, null, \PDO::PARAM_INT)
        );
    }

    /**
     * Returns the active connection.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        return $this->dbHandler;
    }

    /**
     * Stores the file reference in $field for $versionNo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @return bool
     */
    public function storeFileReference(VersionInfo $versionInfo, Field $field)
    {
        $connection = $this->getConnection();

        $insertQuery = $connection->createInsertQuery();
        $insertQuery->insertInto(
            $connection->quoteTable($this->getStorageTable())
        );

        $this->setInsertColumns($insertQuery, $versionInfo, $field);

        $insertQuery->prepare()->execute();

        return false;
    }

    /**
     * Removes the prepended mime-type directory from $path for legacy storage.
     *
     * @param string $path
     *
     * @return string
     */
    public function removeMimeFromPath($path)
    {
        $res = substr($path, strpos($path, '/') + 1);

        return $res;
    }

    /**
     * Returns the file reference data for the given $fieldId in $versionNo.
     *
     * @param mixed $fieldId
     * @param int $versionNo
     *
     * @return array|void
     */
    public function getFileReferenceData($fieldId, $versionNo)
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();

        $this->setFetchColumns($selectQuery, $fieldId, $versionNo);

        $selectQuery->from(
            $connection->quoteTable($this->getStorageTable())
        )->where(
            $selectQuery->expr->lAnd(
                $selectQuery->expr->eq(
                    $connection->quoteColumn('contentobject_attribute_id'),
                    $selectQuery->bindValue($fieldId, null, \PDO::PARAM_INT)
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn('version'),
                    $selectQuery->bindValue($versionNo, null, \PDO::PARAM_INT)
                )
            )
        );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) < 1) {
            return null;
        }

        $convertedResult = [];
        foreach (reset($result) as $column => $value) {
            $convertedResult[$this->toPropertyName($column)] = $this->castToPropertyValue($value, $column);
        }
        $convertedResult['id'] = $this->prependMimeToPath(
            $convertedResult['id'],
            $convertedResult['mimeType']
        );

        return $convertedResult;
    }

    /**
     * Returns the property name for the given $columnName.
     *
     * @param string $columnName
     *
     * @return string
     */
    protected function toPropertyName($columnName)
    {
        $propertyMap = $this->getPropertyMapping();

        return $propertyMap[$columnName]['name'];
    }

    /**
     * Returns $value casted as specified by {@link getPropertyMapping()}.
     *
     * @param mixed $value
     * @param string $columnName
     *
     * @return mixed
     */
    protected function castToPropertyValue($value, $columnName)
    {
        $propertyMap = $this->getPropertyMapping();
        $castFunction = $propertyMap[$columnName]['cast'];

        return $castFunction($value);
    }

    /**
     * Prepends $path with the first part of the given $mimeType.
     *
     * @param string $path
     * @param string $mimeType
     *
     * @return string
     */
    public function prependMimeToPath($path, $mimeType)
    {
        $res = substr($mimeType, 0, strpos($mimeType, '/')) . '/' . $path;

        return $res;
    }

    /**
     * Removes all file references for the given $fieldIds.
     *
     * @param array $fieldIds
     */
    public function removeFileReferences(array $fieldIds, $versionNo)
    {
        if (empty($fieldIds)) {
            return;
        }

        $connection = $this->getConnection();

        $deleteQuery = $connection->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $connection->quoteTable($this->getStorageTable())
        )->where(
            $deleteQuery->expr->lAnd(
                $deleteQuery->expr->in(
                    $connection->quoteColumn('contentobject_attribute_id'),
                    $fieldIds
                ),
                $deleteQuery->expr->eq(
                    $connection->quoteColumn('version'),
                    $deleteQuery->bindValue($versionNo, null, \PDO::PARAM_INT)
                )
            )
        );

        $deleteQuery->prepare()->execute();
    }

    /**
     * Removes a specific file reference for $fieldId and $versionId.
     *
     * @param mixed $fieldId
     * @param int $versionNo
     */
    public function removeFileReference($fieldId, $versionNo)
    {
        $connection = $this->getConnection();

        $deleteQuery = $connection->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $connection->quoteTable($this->getStorageTable())
        )->where(
            $deleteQuery->expr->lAnd(
                $deleteQuery->expr->eq(
                    $connection->quoteColumn('contentobject_attribute_id'),
                    $deleteQuery->bindValue($fieldId, null, \PDO::PARAM_INT)
                ),
                $deleteQuery->expr->eq(
                    $connection->quoteColumn('version'),
                    $deleteQuery->bindValue($versionNo, null, \PDO::PARAM_INT)
                )
            )
        );

        $deleteQuery->prepare()->execute();
    }

    /**
     * Returns a set o file references, referenced by the given $fieldIds.
     *
     * @param array $fieldIds
     *
     * @return array
     */
    public function getReferencedFiles(array $fieldIds, $versionNo)
    {
        if (empty($fieldIds)) {
            return [];
        }

        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $connection->quoteColumn('filename'),
            $connection->quoteColumn('mime_type')
        )->from(
            $connection->quoteTable($this->getStorageTable())
        )->where(
            $selectQuery->expr->lAnd(
                $selectQuery->expr->in(
                    $connection->quoteColumn('contentobject_attribute_id'),
                    $fieldIds
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn('version'),
                    $selectQuery->bindValue($versionNo, null, \PDO::PARAM_INT)
                )
            )
        );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $gateway = $this;

        return array_map(
            function ($row) use ($gateway) {
                return $gateway->prependMimeToPath($row['filename'], $row['mime_type']);
            },
            $statement->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    /**
     * Returns a map with the number of references each file from $files has.
     *
     * @param array $files
     *
     * @return array
     */
    public function countFileReferences(array $files)
    {
        if (empty($files)) {
            return [];
        }

        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $connection->quoteColumn('filename'),
            $connection->quoteColumn('mime_type'),
            $selectQuery->alias(
                $selectQuery->expr->count($connection->quoteColumn('contentobject_attribute_id')),
                'count'
            )
        )->from(
            $connection->quoteTable($this->getStorageTable())
        )->where(
            $selectQuery->expr->in(
                $connection->quoteColumn('filename'),
                array_map(
                    [$this, 'removeMimeFromPath'],
                    $files
                )
            )
        )->groupBy(
            $connection->quoteColumn('filename'),
            $connection->quoteColumn('mime_type')
        );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $countMap = [];
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $path = $this->prependMimeToPath($row['filename'], $row['mime_type']);
            $countMap[$path] = (int)$row['count'];
        }

        // Complete counts
        foreach ($files as $path) {
            // This is already the correct path
            if (!isset($countMap[$path])) {
                $countMap[$path] = 0;
            }
        }

        return $countMap;
    }
}
