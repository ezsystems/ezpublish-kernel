<?php
/**
 * File containing the Content FieldHandler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway as TypeGateway;

/**
 * Field Handler.
 */
class FieldHandler
{
    /**
     * Content Gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * Content Type Gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected $typeGateway;

    /**
     * Content Mapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $mapper;

    /**
     * Storage Handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $storageHandler;

    /**
     * Creates a new Field Handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway $typeGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Mapper $mapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler $storageHandler
     */
    public function __construct(
        Gateway $contentGateway,
        TypeGateway $typeGateway,
        Mapper $mapper,
        StorageHandler $storageHandler )
    {
        $this->contentGateway = $contentGateway;
        $this->typeGateway = $typeGateway;
        $this->mapper = $mapper;
        $this->storageHandler = $storageHandler;
    }

    /**
     * Creates new fields in the database from $content
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return void
     */
    public function createNewFields( Content $content )
    {
        foreach ( $content->fields as $field )
        {
            $this->createNewField( $field, $content );
        }
    }

    /**
     * Creates existing fields in a new version for $content
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return void
     */
    public function createExistingFieldsInNewVersion( Content $content )
    {
        foreach ( $content->fields as $field )
        {
            $this->createExistingFieldInNewVersion( $field, $content );
        }
    }

    /**
     * Creates a new field in the database
     *
     * Used by self::createNewFields() and self::updateFields()
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * return void
     */
    protected function createNewField( Field $field, Content $content )
    {
        $field->versionNo = $content->versionInfo->versionNo;

        $field->id = $this->contentGateway->insertNewField(
            $content,
            $field,
            $this->mapper->convertToStorageValue( $field )
        );

        // If the storage handler returns true, it means that $field value has been modified
        // So we need to update it in order to store those modifications
        // Field converter is called once again via the Mapper
        if ( $this->storageHandler->storeFieldData( $content->versionInfo, $field ) === true )
        {
            $this->contentGateway->updateField(
                $field,
                $this->mapper->convertToStorageValue( $field )
            );
        }
    }

    /**
     * Creates an existing field in a new version, no new ID is generated
     *
     * @param Field $field
     * @param Content $content
     *
     * @return void
     */
    public function createExistingFieldInNewVersion( Field $field, Content $content )
    {
        $field->versionNo = $content->versionInfo->versionNo;

        $this->contentGateway->insertExistingField(
            $content,
            $field,
            $this->mapper->convertToStorageValue( $field )
        );

        // If the storage handler returns true, it means that $field value has been modified
        // So we need to update it in order to store those modifications
        // Field converter is called once again via the Mapper
        if ( $this->storageHandler->storeFieldData( $content->versionInfo, $field ) === true )
        {
            $this->contentGateway->updateField(
                $field,
                $this->mapper->convertToStorageValue( $field )
            );
        }
    }

    /**
     * Performs external loads for the fields in $content
     *
     * @param Content $content
     *
     * @return void
     */
    public function loadExternalFieldData( Content $content )
    {
        foreach ( $content->fields as $field )
        {
            $this->storageHandler->getFieldData( $content->versionInfo, $field );
        }
    }

    /**
     * Updates the fields in for content identified by $contentId and $versionNo in the database in respect to $updateStruct
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\UpdateStruct $updateStruct
     *
     * @return void
     */
    public function updateFields( $content, UpdateStruct $updateStruct )
    {
        foreach ( $updateStruct->fields as $field )
        {
            $field->versionNo = $content->versionInfo->versionNo;
            if ( isset( $field->id ) )
            {
                $this->contentGateway->updateField(
                    $field,
                    $this->mapper->convertToStorageValue( $field )
                );
            }
            else
            {
                $this->createNewField( $field, $content );
            }

            // If the storage handler returns true, it means that $field value has been modified
            // So we need to update it in order to store those modifications
            // Field converter is called once again via the Mapper
            if ( $this->storageHandler->storeFieldData( $content->versionInfo, $field ) === true )
            {
                $this->contentGateway->updateField(
                    $field,
                    $this->mapper->convertToStorageValue( $field )
                );
            }
        }
    }

    /**
     * Deletes the fields for $contentId in $versionInfo from the database
     *
     * @param int $contentId
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     *
     * @return void
     */
    public function deleteFields( $contentId, VersionInfo $versionInfo )
    {
        foreach ( $this->contentGateway->getFieldIdsByType( $contentId, $versionInfo->versionNo )
            as $fieldType => $ids )
        {
            $this->storageHandler->deleteFieldData( $fieldType, $versionInfo, $ids );
        }
        $this->contentGateway->deleteFields( $contentId, $versionInfo->versionNo );
    }
}
