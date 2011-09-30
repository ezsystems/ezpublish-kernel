<?php
/**
 * File containing the Content FieldHandler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Content;
use ezp\Persistence\Content,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\Version,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Storage\Legacy\Content\FieldHandler,
    ezp\Persistence\Storage\Legacy\Content\Mapper,
    ezp\Persistence\Storage\Legacy\Content\Gateway;

/**
 * Field Handler.
 */
class FieldHandler
{
    /**
     * Content Gateway
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * Content Type Gateway
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     */
    protected $typeGateway;

    /**
     * Content Mapper
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Mapper
     */
    protected $mapper;

    /**
     * Storage Handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\StorageHandler
     */
    protected $storageHandler;

    /**
     * Creates a new Field Handler
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Gateway $contentGateway
     * @param \ezp\Persistence\Storage\Legacy\Content\Type\Gateway $typeGateway
     * @param \ezp\Persistence\Storage\Legacy\Content\Mapper $mapper
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageHandler $storageHandler
     */
    public function __construct(
        Gateway $contentGateway,
        Type\Gateway $typeGateway,
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
     * @param Content $content
     * @return void
     */
    public function createNewFields( Content $content )
    {
        foreach ( $content->version->fields as $field )
        {
            $field->versionNo = $content->version->versionNo;
            $field->id = $this->contentGateway->insertNewField(
                $content,
                $field,
                $this->mapper->convertToStorageValue( $field )
            );
            $this->storageHandler->storeFieldData( $field );
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
        foreach ( $content->version->fields as $field )
        {
            $this->storageHandler->getFieldData( $field );
        }
    }

    /**
     * Updates the fields in $content in the database
     *
     * @param Content $content
     * @return void
     */
    public function updateFields( UpdateStruct $updateStruct )
    {
        foreach ( $updateStruct->fields as $field )
        {
            $field->versionNo = $updateStruct->versionNo;

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
                    $updateStruct
                );
            }

            $this->storageHandler->storeFieldData( $field );
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
