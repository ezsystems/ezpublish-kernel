<?php
/**
 * File containing the MediaStorage Gateway
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Media\MediaStorage\Gateway;

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
        return 'ezmedia';
    }

    /**
     * Returns a column to property mapping for the storage table.
     *
     * @return void
     */
    protected function getPropertyMapping()
    {
        $propertyMap = parent::getPropertyMapping();
        $propertyMap['has_controller'] = array(
            'name' => 'hasController',
            'cast' =>
                function ( $val )
                {
                    return (bool)$val;
                },
        );
        $propertyMap['is_autoplay'] = array(
            'name' => 'autoplay',
            'cast' =>
                function ( $val )
                {
                    return (bool)$val;
                },
        );
        $propertyMap['is_loop'] = array(
            'name' => 'loop',
            'cast' =>
                function ( $val )
                {
                    return (bool)$val;
                },
        );
        $propertyMap['width'] = array(
            'name' => 'width',
            'cast' => 'intval',
        );
        $propertyMap['height'] = array(
            'name' => 'height',
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
            $connection->quoteColumn( 'has_controller' ),
            $connection->quoteColumn( 'is_autoplay' ),
            $connection->quoteColumn( 'is_loop' ),
            $connection->quoteColumn( 'width' ),
            $connection->quoteColumn( 'height' )
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
            $connection->quoteColumn( 'controls' ),
            $insertQuery->bindValue( '' )
        )->set(
            $connection->quoteColumn( 'has_controller' ),
            $insertQuery->bindValue( (int)$field->value->externalData['hasController'], null, \PDO::PARAM_INT )
        )->set(
            $connection->quoteColumn( 'height' ),
            $insertQuery->bindValue( (int)$field->value->externalData['height'], null, \PDO::PARAM_INT )
        )->set(
            $connection->quoteColumn( 'is_autoplay' ),
            $insertQuery->bindValue( (int)$field->value->externalData['autoplay'], null, \PDO::PARAM_INT )
        )->set(
            $connection->quoteColumn( 'is_loop' ),
            $insertQuery->bindValue( (int)$field->value->externalData['loop'], null, \PDO::PARAM_INT )
        )->set(
            $connection->quoteColumn( 'pluginspage' ),
            $insertQuery->bindValue( '' )
        )->set(
            $connection->quoteColumn( 'quality' ),
            $insertQuery->bindValue( 'high' )
        )->set(
            $connection->quoteColumn( 'width' ),
            $insertQuery->bindValue( (int)$field->value->externalData['width'], null, \PDO::PARAM_INT )
        );
    }
}

