<?php
/**
 * File containing the BinaryFileStorage Gateway
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\BinaryFile\BinaryFileStorage\Gateway;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\FieldType\BinaryBase\BinaryBaseStorage\Gateway\LegacyStorage as BaseLegacyStorage;

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
     * @return void
     */
    protected function getPropertyMapping()
    {
        $propertyMap = parent::getPropertyMapping();
        $propertyMap['download_count'] = array(
            'name' => 'downloadCount',
            'cast' => 'intval',
        );
        return $propertyMap;
    }

    /**
     * Set columns to be fetched from the database
     *
     * This method is intended to be overwritten by derived classes in order to
     * add additional columns to be fetched from the database. Please do not
     * forget to call the parent when overwriting this method.
     *
     * @param \ezcQuerySelect $selectQuery
     * @param int $fieldId
     * @param int $versionNo
     *
     * @return void
     */
    protected function setFetchColumns( \ezcQuerySelect $selectQuery, $fieldId, $versionNo )
    {
        $connection = $this->getConnection();

        parent::setFetchColumns( $selectQuery, $fieldId, $versionNo );
        $selectQuery->select(
            $connection->quoteColumn( 'download_count' )
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
     *
     * @return void
     */
    protected function setInsertColumns( \ezcQueryInsert $insertQuery, VersionInfo $versionInfo, Field $field )
    {
        $connection = $this->getConnection();

        parent::setInsertColumns( $insertQuery, $versionInfo, $field );
        $insertQuery->set(
            $connection->quoteColumn( 'download_count' ),
            $insertQuery->bindValue( $field->value->externalData['downloadCount'], null, \PDO::PARAM_INT )
        );
    }
}

