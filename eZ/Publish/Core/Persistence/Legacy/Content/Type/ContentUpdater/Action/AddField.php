<?php
/**
 * File containing the content updater add field action class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action;

use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;

/**
 * Action to add a field to content objects
 */
class AddField extends Action
{
    /**
     * Field definition of the field to add
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    protected $fieldDefinition;

    /**
     * Storage handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $storageHandler;

    /**
     * Field value converter
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter
     */
    protected $fieldValueConverter;

    /**
     * Creates a new action
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter $converter
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler $storageHandler
     */
    public function __construct(
        Gateway $contentGateway,
        FieldDefinition $fieldDef,
        Converter $converter,
        StorageHandler $storageHandler )
    {
        $this->contentGateway = $contentGateway;
        $this->fieldDefinition = $fieldDef;
        $this->fieldValueConverter = $converter;
        $this->storageHandler = $storageHandler;
    }

    /**
     * Applies the action to the given $content
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    public function apply( Content $content )
    {
        $languageCodeSet = array();
        $versionNumbers = $this->contentGateway->listVersionNumbers(
            $content->versionInfo->contentInfo->id
        );

        foreach ( $content->fields as $field )
        {
            if ( isset( $languageCodeSet[$field->languageCode] ) )
            {
                continue;
            }

            $languageCodeSet[$field->languageCode] = true;

            foreach ( $versionNumbers as $versionNo )
            {
                $this->insertField(
                    $content,
                    $this->createField( $versionNo, $field->languageCode )
                );
            }
        }
    }

    /**
     * Inserts given $field and appends it to the given $content field collection.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return void
     */
    protected function insertField( Content $content, Field $field )
    {
        $storageValue = new StorageFieldValue();
        $this->fieldValueConverter->toStorageValue(
            $field->value,
            $storageValue
        );

        $field->id = $this->contentGateway->insertNewField(
            $content,
            $field,
            $storageValue
        );

        // If the storage handler returns true, it means that $field value has been modified
        // So we need to update it in order to store those modifications
        // Field converter is called once again via the Mapper
        if ( $this->storageHandler->storeFieldData( $content->versionInfo, $field ) === true )
        {
            $storageValue = new StorageFieldValue();
            $this->fieldValueConverter->toStorageValue(
                $field->value,
                $storageValue
            );

            if ( $this->fieldDefinition->isTranslatable )
            {
                $this->contentGateway->updateField(
                    $field,
                    $storageValue
                );
            }
            else
            {
                $this->contentGateway->updateNonTranslatableField(
                    $field,
                    $storageValue,
                    $content->versionInfo->contentInfo->id
                );
            }
        }

        $content->fields[] = $field;
    }

    /**
     *
     *
     * @param int $versionNo
     * @param string $languageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field
     */
    protected function createField( $versionNo, $languageCode )
    {
        $field = new Field();

        $field->fieldDefinitionId = $this->fieldDefinition->id;
        $field->type = $this->fieldDefinition->fieldType;
        $field->value = clone $this->fieldDefinition->defaultValue;
        $field->versionNo = $versionNo;
        $field->languageCode = $languageCode;

        return $field;
    }
}
