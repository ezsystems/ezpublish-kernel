<?php

/**
 * File containing the MapLocationStorage Gateway.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\MapLocation\MapLocationStorage\Gateway;

use eZ\Publish\Core\FieldType\MapLocation\MapLocationStorage\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * @deprecated since 6.11. Use {@see \eZ\Publish\Core\FieldType\MapLocation\MapLocationStorage\Gateway\DoctrineStorage} instead.
 */
class LegacyStorage extends Gateway
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
     * Returns the active connection.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        return $this->dbHandler;
    }

    /**
     * Stores the data stored in the given $field.
     *
     * Potentially rewrites data in $field and returns true, if the $field
     * needs to be updated in the database.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return bool If restoring of the internal field data is required
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field)
    {
        if ($field->value->externalData === null) {
            // Store empty value and return
            $this->deleteFieldData($versionInfo, [$field->id]);
            $field->value->data = [
                'sortKey' => null,
                'hasData' => false,
            ];

            return false;
        }

        if ($this->hasFieldData($field->id, $versionInfo->versionNo)) {
            $this->updateFieldData($versionInfo, $field);
        } else {
            $this->storeNewFieldData($versionInfo, $field);
        }

        $field->value->data = [
            'sortKey' => $field->value->externalData['address'],
            'hasData' => true,
        ];

        return true;
    }

    /**
     * Performs an update on the field data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return bool
     */
    protected function updateFieldData(VersionInfo $versionInfo, Field $field)
    {
        $connection = $this->getConnection();

        $updateQuery = $connection->createUpdateQuery();
        $updateQuery->update($connection->quoteTable('ezgmaplocation'))
            ->set(
                $connection->quoteColumn('latitude'),
                $updateQuery->bindValue($field->value->externalData['latitude'])
            )->set(
                $connection->quoteColumn('longitude'),
                $updateQuery->bindValue($field->value->externalData['longitude'])
            )->set(
                $connection->quoteColumn('address'),
                $updateQuery->bindValue($field->value->externalData['address'])
            )->where(
                $updateQuery->expr->lAnd(
                    $updateQuery->expr->eq(
                        $connection->quoteColumn('contentobject_attribute_id'),
                        $updateQuery->bindValue($field->id, null, \PDO::PARAM_INT)
                    ),
                    $updateQuery->expr->eq(
                        $connection->quoteColumn('contentobject_version'),
                        $updateQuery->bindValue($versionInfo->versionNo, null, \PDO::PARAM_INT)
                    )
                )
            );

        $updateQuery->prepare()->execute();
    }

    /**
     * Stores new field data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    protected function storeNewFieldData(VersionInfo $versionInfo, Field $field)
    {
        $connection = $this->getConnection();

        $insertQuery = $connection->createInsertQuery();
        $insertQuery->insertInto($connection->quoteTable('ezgmaplocation'))
            ->set(
                $connection->quoteColumn('latitude'),
                $insertQuery->bindValue($field->value->externalData['latitude'])
            )->set(
                $connection->quoteColumn('longitude'),
                $insertQuery->bindValue($field->value->externalData['longitude'])
            )->set(
                $connection->quoteColumn('address'),
                $insertQuery->bindValue($field->value->externalData['address'])
            )->set(
                $connection->quoteColumn('contentobject_attribute_id'),
                $insertQuery->bindValue($field->id, null, \PDO::PARAM_INT)
            )->set(
                $connection->quoteColumn('contentobject_version'),
                $insertQuery->bindValue($versionInfo->versionNo, null, \PDO::PARAM_INT)
            );

        $insertQuery->prepare()->execute();
    }

    /**
     * Sets the loaded field data into $field->externalData.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return array
     */
    public function getFieldData(VersionInfo $versionInfo, Field $field)
    {
        $field->value->externalData = $this->loadFieldData($field->id, $versionInfo->versionNo);
    }

    /**
     * Returns the data for the given $fieldId.
     *
     * If no data is found, null is returned.
     *
     * @param int $fieldId
     *
     * @return array|null
     */
    protected function loadFieldData($fieldId, $versionNo)
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $connection->quoteColumn('latitude'),
            $connection->quoteColumn('longitude'),
            $connection->quoteColumn('address')
        )->from(
            $connection->quoteTable('ezgmaplocation')
        )->where(
            $selectQuery->expr->lAnd(
                $selectQuery->expr->eq(
                    $connection->quoteColumn('contentobject_attribute_id'),
                    $selectQuery->bindValue($fieldId, null, \PDO::PARAM_INT)
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn('contentobject_version'),
                    $selectQuery->bindValue($versionNo, null, \PDO::PARAM_INT)
                )
            )
        );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!isset($rows[0])) {
            return null;
        }

        // Cast coordinates as the DB can return them as strings
        $rows[0]['latitude'] = (float)$rows[0]['latitude'];
        $rows[0]['longitude'] = (float)$rows[0]['longitude'];

        return $rows[0];
    }

    /**
     * Returns if field data exists for $fieldId.
     *
     * @param int $fieldId
     * @param int $versionNo
     *
     * @return bool
     */
    protected function hasFieldData($fieldId, $versionNo)
    {
        return $this->loadFieldData($fieldId, $versionNo) !== null;
    }

    /**
     * Deletes the data for all given $fieldIds.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     */
    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds)
    {
        if (empty($fieldIds)) {
            // Nothing to do
            return;
        }

        $connection = $this->getConnection();

        $deleteQuery = $connection->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $connection->quoteTable('ezgmaplocation')
        )->where(
            $deleteQuery->expr->lAnd(
                $deleteQuery->expr->in(
                    $connection->quoteColumn('contentobject_attribute_id'),
                    $fieldIds
                ),
                $deleteQuery->expr->eq(
                    $connection->quoteColumn('contentobject_version'),
                    $deleteQuery->bindValue($versionInfo->versionNo, null, \PDO::PARAM_INT)
                )
            )
        );

        $deleteQuery->prepare()->execute();
    }
}
