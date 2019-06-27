<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\BinaryBase\BinaryBaseStorage\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\FieldType\BinaryBase\BinaryBaseStorage\Gateway;
use PDO;

/**
 * Base class for binary files external storage DoctrineStorage gateways.
 */
abstract class DoctrineStorage extends Gateway
{
    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Return the table name to store data in.
     *
     * @return string
     */
    abstract protected function getStorageTable();

    /**
     * Return a column to property mapping for the storage table.
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
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     * @param int $fieldId
     * @param int $versionNo
     */
    protected function setFetchColumns(QueryBuilder $queryBuilder, $fieldId, $versionNo)
    {
        $queryBuilder->select(
            $this->connection->quoteIdentifier('filename'),
            $this->connection->quoteIdentifier('mime_type'),
            $this->connection->quoteIdentifier('original_filename')
        );
    }

    /**
     * Set the required insert columns to insert query builder.
     *
     * This method is intended to be overwritten by derived classes in order to
     * add additional columns to be set in the database. Please do not forget
     * to call the parent when overwriting this method.
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    protected function setInsertColumns(QueryBuilder $queryBuilder, VersionInfo $versionInfo, Field $field)
    {
        $queryBuilder
            ->setValue('contentobject_attribute_id', ':fieldId')
            ->setValue('filename', ':filename')
            ->setValue('mime_type', ':mimeType')
            ->setValue('original_filename', ':originalFilename')
            ->setValue('version', ':versionNo')
            ->setParameter(':fieldId', $field->id, PDO::PARAM_INT)
            ->setParameter(':filename', $this->removeMimeFromPath($field->value->externalData['id']))
            ->setParameter(':mimeType', $field->value->externalData['mimeType'])
            ->setParameter(':originalFilename', $field->value->externalData['fileName'])
            ->setParameter(':versionNo', $versionInfo->versionNo, PDO::PARAM_INT)
        ;
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    protected function setUpdateColumns(QueryBuilder $queryBuilder, VersionInfo $versionInfo, Field $field)
    {
        $queryBuilder
            ->set('contentobject_attribute_id', ':fieldId')
            ->set('filename', ':filename')
            ->set('mime_type', ':mimeType')
            ->set('original_filename', ':originalFilename')
            ->set('version', ':versionNo')
            ->setParameter(':fieldId', $field->id, ParameterType::INTEGER)
            ->setParameter(':filename', $this->removeMimeFromPath($field->value->externalData['id']))
            ->setParameter(':mimeType', $field->value->externalData['mimeType'])
            ->setParameter(':originalFilename', $field->value->externalData['fileName'])
            ->setParameter(':versionNo', $versionInfo->versionNo, ParameterType::INTEGER)
        ;
    }

    /**
     * Store the file reference in $field for $versionNo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @return bool
     */
    public function storeFileReference(VersionInfo $versionInfo, Field $field)
    {
        $referencedData = $this->getFileReferenceData($field->id, $versionInfo->versionNo);

        if ($referencedData === null) {
            $this->storeNewFieldData($versionInfo, $field);
        } elseif (is_array($referencedData) && !empty(array_diff_assoc($referencedData, $field->value->externalData))) {
            $this->updateFieldData($versionInfo, $field);
        }

        return false;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    protected function updateFieldData(VersionInfo $versionInfo, Field $field)
    {
        $updateQuery = $this->connection->createQueryBuilder();
        $updateQuery->update(
            $this->connection->quoteIdentifier($this->getStorageTable())
        );

        $this->setUpdateColumns($updateQuery, $versionInfo, $field);
        $updateQuery
            ->where(
                $updateQuery->expr()->andX(
                    $updateQuery->expr()->eq(
                        $this->connection->quoteIdentifier('contentobject_attribute_id'),
                        ':fieldId'
                    ),
                    $updateQuery->expr()->eq(
                        $this->connection->quoteIdentifier('version'),
                        ':versionNo'
                    )
                )
            )
            ->setParameter(':fieldId', $field->id, ParameterType::INTEGER)
            ->setParameter(':versionNo', $versionInfo->versionNo, ParameterType::INTEGER)
        ;

        $updateQuery->execute();
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    protected function storeNewFieldData(VersionInfo $versionInfo, Field $field)
    {
        $insertQuery = $this->connection->createQueryBuilder();
        $insertQuery->insert(
            $this->connection->quoteIdentifier($this->getStorageTable())
        );

        $this->setInsertColumns($insertQuery, $versionInfo, $field);

        $insertQuery->execute();
    }

    /**
     * Remove the prepended mime-type directory from $path for legacy storage.
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
     * Return the file reference data for the given $fieldId in $versionNo.
     *
     * @param int $fieldId
     * @param int $versionNo
     *
     * @return array|null
     */
    public function getFileReferenceData($fieldId, $versionNo)
    {
        $selectQuery = $this->connection->createQueryBuilder();

        $this->setFetchColumns($selectQuery, $fieldId, $versionNo);

        $selectQuery
            ->from($this->connection->quoteIdentifier($this->getStorageTable()))
            ->where(
                $selectQuery->expr()->andX(
                    $selectQuery->expr()->eq(
                        $this->connection->quoteIdentifier('contentobject_attribute_id'),
                        ':fieldId'
                    ),
                    $selectQuery->expr()->eq(
                        $this->connection->quoteIdentifier('version'),
                        ':versionNo'
                    )
                )
            )
            ->setParameter(':fieldId', $fieldId, PDO::PARAM_INT)
            ->setParameter(':versionNo', $versionNo, PDO::PARAM_INT)
        ;

        $statement = $selectQuery->execute();

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

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
     * Return the property name for the given $columnName.
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
     * Return $value casted as specified by {@link getPropertyMapping()}.
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
     * Prepend $path with the first part of the given $mimeType.
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
     * Remove all file references for the given $fieldIds.
     *
     * @param array $fieldIds
     * @param int $versionNo
     */
    public function removeFileReferences(array $fieldIds, $versionNo)
    {
        if (empty($fieldIds)) {
            return;
        }

        $deleteQuery = $this->connection->createQueryBuilder();
        $deleteQuery
            ->delete($this->connection->quoteIdentifier($this->getStorageTable()))
            ->where(
                $deleteQuery->expr()->andX(
                    $deleteQuery->expr()->in(
                        $this->connection->quoteIdentifier('contentobject_attribute_id'),
                        ':fieldIds'
                    ),
                    $deleteQuery->expr()->eq(
                        $this->connection->quoteIdentifier('version'),
                        ':versionNo'
                    )
                )
            )
            ->setParameter(':fieldIds', $fieldIds, Connection::PARAM_INT_ARRAY)
            ->setParameter(':versionNo', $versionNo, PDO::PARAM_INT)
        ;

        $deleteQuery->execute();
    }

    /**
     * Remove a specific file reference for $fieldId and $versionId.
     *
     * @param int $fieldId
     * @param int $versionNo
     */
    public function removeFileReference($fieldId, $versionNo)
    {
        $deleteQuery = $this->connection->createQueryBuilder();
        $deleteQuery
            ->delete($this->connection->quoteIdentifier($this->getStorageTable()))
            ->where(
                $deleteQuery->expr()->andX(
                    $deleteQuery->expr()->eq(
                        $this->connection->quoteIdentifier('contentobject_attribute_id'),
                        ':fieldId'
                    ),
                    $deleteQuery->expr()->eq(
                        $this->connection->quoteIdentifier('version'),
                        ':versionNo'
                    )
                )
            )
            ->setParameter(':fieldId', $fieldId, PDO::PARAM_INT)
            ->setParameter(':versionNo', $versionNo, PDO::PARAM_INT)
        ;

        $deleteQuery->execute();
    }

    /**
     * Return a set o file references, referenced by the given $fieldIds.
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

        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery
            ->select(
                $this->connection->quoteIdentifier('filename'),
                $this->connection->quoteIdentifier('mime_type')
            )
            ->from($this->connection->quoteIdentifier($this->getStorageTable()))
            ->where(
                $selectQuery->expr()->andX(
                    $selectQuery->expr()->in(
                        $this->connection->quoteIdentifier('contentobject_attribute_id'),
                        ':fieldIds'
                    ),
                    $selectQuery->expr()->eq(
                        $this->connection->quoteIdentifier('version'),
                        ':versionNo'
                    )
                )
            )
            ->setParameter(':fieldIds', $fieldIds, Connection::PARAM_INT_ARRAY)
            ->setParameter(':versionNo', $versionNo, PDO::PARAM_INT)
        ;

        $statement = $selectQuery->execute();

        return array_map(
            function ($row) {
                return $this->prependMimeToPath($row['filename'], $row['mime_type']);
            },
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * Return a map with the number of references each file from $files has.
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

        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery
            ->select(
                $this->connection->quoteIdentifier('filename'),
                $this->connection->quoteIdentifier('mime_type'),
                sprintf(
                    'COUNT(%s) AS count',
                    $this->connection->quoteIdentifier('contentobject_attribute_id')
                )
            )
            ->from($this->connection->quoteIdentifier($this->getStorageTable()))
            ->where(
                $selectQuery->expr()->in(
                    $this->connection->quoteIdentifier('filename'),
                    ':filenames'
                )
            )
            ->groupBy(
                $this->connection->quoteIdentifier('filename'),
                $this->connection->quoteIdentifier('mime_type')
            )
            ->setParameter(
                ':filenames',
                array_map(
                    [$this, 'removeMimeFromPath'],
                    $files
                ),
                Connection::PARAM_STR_ARRAY
            )
        ;

        $statement = $selectQuery->execute();

        $countMap = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
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
