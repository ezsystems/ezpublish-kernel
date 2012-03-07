<?php
/**
 * File containing the Content FieldHandler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content;
use eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\Version,
    eZ\Publish\SPI\Persistence\Content\UpdateStruct,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Mapper,
    eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway as TypeGateway,
    eZ\Publish\Core\Persistence\Legacy\Content\Gateway;

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
     * @return void
     */
    public function createNewFields( Content $content )
    {
        foreach ( $content->fields as $field )
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
            if ( $this->storageHandler->storeFieldData( $field ) === true )
            {
                $this->contentGateway->updateField(
                    $field,
                    $this->mapper->convertToStorageValue( $field )
                );
            }
        }
    }

    /**
     * Performs external loads for the fields in $content
     *
     * @param Content $content
     * @return void
     */
    public function loadExternalFieldData( Content $content )
    {
        foreach ( $content->fields as $field )
        {
            $this->storageHandler->getFieldData( $field );
        }
    }

    /**
     * Updates the fields in for content identified by $contentId and $versionNo in the database in respect to $updateStruct
     *
     * @param int $contentId
     * @param int $versionNo
     * @param \eZ\Publish\SPI\Persistence\Content\UpdateStruct $updateStruct
     * @return void
     */
    public function updateFields( $contentId, $versionNo, UpdateStruct $updateStruct )
    {
        foreach ( $updateStruct->fields as $field )
        {
            $field->versionNo = $versionNo;

            if (
                $this->typeGateway->isFieldTranslatable(
                    $field->fieldDefinitionId,
                    0
                )
            )
            {
                $this->contentGateway->updateField(
                    $field,
                    $this->mapper->convertToStorageValue( $field )
                );
            }
            else
            {
                $this->contentGateway->updateNonTranslatableField(
                    $field,
                    $this->mapper->convertToStorageValue( $field ),
                    $contentId
                );
            }

            // If the storage handler returns true, it means that $field value has been modified
            // So we need to update it in order to store those modifications
            // Field converter is called once again via the Mapper
            if ( $this->storageHandler->storeFieldData( $field ) === true )
            {
                $this->contentGateway->updateField(
                    $field,
                    $this->mapper->convertToStorageValue( $field )
                );
            }
        }
    }

    /**
     * Deletes the fields in $content from the database
     *
     * @param Content $content
     * @return void
     */
    public function deleteFields( $contentId )
    {
        $fieldIds = $this->contentGateway->getFieldIdsByType( $contentId );
        foreach ( $fieldIds as $fieldType => $ids )
        {
            $this->storageHandler->deleteFieldData( $fieldType, $ids );
        }
        $this->contentGateway->deleteFields( $contentId );
    }
}
