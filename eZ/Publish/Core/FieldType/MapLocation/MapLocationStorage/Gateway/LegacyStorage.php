<?php
/**
 * File containing the MapLocationStorage Gateway
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\MapLocation\MapLocationStorage\Gateway;
use eZ\Publish\Core\FieldType\MapLocation\MapLocationStorage\Gateway,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\VersionInfo;

class LegacyStorage extends Gateway
{
    /**
     * Connection
     *
     * @var mixed
     */
    protected $dbHandler;

    /**
     * Set database handler for this gateway
     *
     * @param mixed $dbHandler
     * @return void
     * @throws \RuntimeException if $dbHandler is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler}
     */
    public function setConnection( $dbHandler )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( !$dbHandler instanceof \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler )
        {
            throw new \RuntimeException( "Invalid dbHandler passed" );
        }

        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the active connection
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     * @throws \RuntimeException if no connection has been set, yet.
     */
    protected function getConnection()
    {
        if ( $this->dbHandler === null )
        {
            throw new \RuntimeException( "Missing database connection." );
        }
        return $this->dbHandler;
    }

    /**
     * Stores the data stored in the given $field
     *
     * Potentially rewrites data in $field and returns true, if the $field
     * needs to be updated in the database.
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     * @return bool If restoring of the internal field data is required
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field )
    {
        if ( $field->value->externalData === null )
        {
            // Store empty value and return
            $this->deleteFieldData( $versionInfo, array( $field->id ) );
            $field->value->data = array(
                'sortKey' => null,
                'hasData' => false,
            );
            return;
        }

        if ( $this->hasFieldData( $field->id, $versionInfo->versionNo ) )
        {
            $this->updateFieldData( $versionInfo, $field );
        }
        else
        {
            $this->storeNewFieldData( $versionInfo, $field );
        }

        $field->value->data = array(
            'sortKey' => $field->value->externalData['address'],
            'hasData' => true,
        );
        return true;
    }

    /**
     * Performs an update on the field data
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     * @return bool
     */
    protected function updateFieldData( VersionInfo $versionInfo, Field $field )
    {
        $connection = $this->getConnection();

        $updateQuery = $connection->createUpdateQuery();
        $updateQuery->update( $connection->quoteTable( 'ezgmaplocation' ) )
            ->set(
                $connection->quoteColumn( 'latitude' ),
                $updateQuery->bindValue( $field->value->externalData['latitude'] )
            )->set(
                $connection->quoteColumn( 'longitude' ),
                $updateQuery->bindValue( $field->value->externalData['longitude'] )
            )->set(
                $connection->quoteColumn( 'address' ),
                $updateQuery->bindValue( $field->value->externalData['address'] )
            )->where(
                $updateQuery->expr->lAnd(
                    $updateQuery->expr->eq(
                        $connection->quoteColumn( 'contentobject_attribute_id' ),
                        $updateQuery->bindValue( $field->id, null, \PDO::PARAM_INT )
                    ),
                    $updateQuery->expr->eq(
                        $connection->quoteColumn( 'contentobject_version' ),
                        $updateQuery->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
                    )
                )
            );

        $updateQuery->prepare()->execute();
    }

    /**
     * Stores new field data
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     * @return void
     */
    protected function storeNewFieldData( VersionInfo $versionInfo, Field $field )
    {
        $connection = $this->getConnection();

        $insertQuery = $connection->createInsertQuery();
        $insertQuery->insertInto( $connection->quoteTable( 'ezgmaplocation' ) )
            ->set(
                $connection->quoteColumn( 'latitude' ),
                $insertQuery->bindValue( $field->value->externalData['latitude'] )
            )->set(
                $connection->quoteColumn( 'longitude' ),
                $insertQuery->bindValue( $field->value->externalData['longitude'] )
            )->set(
                $connection->quoteColumn( 'address' ),
                $insertQuery->bindValue( $field->value->externalData['address'] )
            )->set(
                $connection->quoteColumn( 'contentobject_attribute_id' ),
                $insertQuery->bindValue( $field->id, null, \PDO::PARAM_INT )
            )->set(
                $connection->quoteColumn( 'contentobject_version' ),
                $insertQuery->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
            );

        $insertQuery->prepare()->execute();
    }

    /**
     * Sets the loaded field data into $field->externalData.
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     * @return array
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field )
    {
        $field->value->externalData = $this->loadFieldData( $field->id, $versionInfo->versionNo );
    }

    /**
     * Returns the data for the given $fieldId
     *
     * If no data is found, null is returned.
     *
     * @param int $fieldId
     * @return array|null
     */
    protected function loadFieldData( $fieldId, $versionNo )
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $connection->quoteColumn( 'latitude' ),
            $connection->quoteColumn( 'longitude' ),
            $connection->quoteColumn( 'address' )
        )->from(
            $connection->quoteTable( 'ezgmaplocation' )
        )->where(
            $selectQuery->expr->lAnd(
                $selectQuery->expr->eq(
                    $connection->quoteColumn( 'contentobject_attribute_id' ),
                    $selectQuery->bindValue( $fieldId, null, \PDO::PARAM_INT )
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn( 'contentobject_version' ),
                    $selectQuery->bindValue( $versionNo, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );

        return ( isset( $rows[0] ) ? $rows[0] : null );
    }

    /**
     * Returns if field data exists for $fieldId
     *
     * @param int $fieldId
     * @param int $versionNo
     * @return bool
     */
    protected function hasFieldData( $fieldId, $versionNo )
    {
        return ( $this->loadFieldData( $fieldId, $versionNo ) !== null );
    }

    /**
     * Deletes the data for all given $fieldIds
     *
     * @param VersionInfo $versionInfo
     * @param array $fieldIds
     * @return void
     */
    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds )
    {
        if ( empty( $fieldIds ) )
        {
            // Nothing to do
            return;
        }

        $connection = $this->getConnection();

        $deleteQuery = $connection->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $connection->quoteTable( 'ezgmaplocation' )
        )->where(
            $deleteQuery->expr->lAnd(
                $deleteQuery->expr->in(
                    $connection->quoteColumn( 'contentobject_attribute_id' ),
                    $fieldIds
                ),
                $deleteQuery->expr->eq(
                    $connection->quoteColumn( 'contentobject_version' ),
                    $deleteQuery->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
                )
            )
        );

        $deleteQuery->prepare()->execute();
    }
}

