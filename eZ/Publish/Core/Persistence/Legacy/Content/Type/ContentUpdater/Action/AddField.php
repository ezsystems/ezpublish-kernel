<?php
/**
 * File containing the content updater add field action class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;
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
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $contentMapper;

    /**
     * Creates a new action
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter $converter
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler $storageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Mapper $contentMapper
     */
    public function __construct(
        Gateway $contentGateway,
        FieldDefinition $fieldDef,
        Converter $converter,
        StorageHandler $storageHandler,
        ContentMapper $contentMapper
    )
    {
        $this->contentGateway = $contentGateway;
        $this->fieldDefinition = $fieldDef;
        $this->fieldValueConverter = $converter;
        $this->storageHandler = $storageHandler;
        $this->contentMapper = $contentMapper;
    }

    /**
     * Applies the action to the given $content
     *
     * @param int $contentId
     */
    public function apply( $contentId )
    {
        $versionNumbers = $this->contentGateway->listVersionNumbers( $contentId );
        $languageCodeToFieldId = array();

        foreach ( $versionNumbers as $versionNo )
        {
            $contentRows = $this->contentGateway->load( $contentId, $versionNo );
            $contentList = $this->contentMapper->extractContentFromRows( $contentRows );
            $content = $contentList[0];
            $languageCodeSet = array();

            // Each subsequent Content version can have additional language(s)
            foreach ( $content->fields as $field )
            {
                $languageCode = $field->languageCode;

                // Add once for each language per version
                if ( isset( $languageCodeSet[$languageCode] ) )
                {
                    continue;
                }

                $languageCodeSet[$languageCode] = true;

                // Check if field was already inserted for current language code,
                // in that case we need to preserve its ID across versions
                if ( isset( $languageCodeToFieldId[$languageCode] ) )
                {
                    $fieldId = $languageCodeToFieldId[$languageCode];
                }
                else
                {
                    $fieldId = null;
                }

                $languageCodeToFieldId[$languageCode] = $this->insertField(
                    $content,
                    $this->createField(
                        $fieldId,
                        $versionNo,
                        $languageCode
                    )
                );
            }
        }
    }

    /**
     * Inserts given $field to the internal and external storage.
     *
     * If $field->id is null, creating new field id will be created.
     * Otherwise it will be inserted for the given $content version, reusing existing Field id.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return int $fieldId
     */
    protected function insertField( Content $content, Field $field )
    {
        $storageValue = new StorageFieldValue();
        $this->fieldValueConverter->toStorageValue(
            $field->value,
            $storageValue
        );

        if ( isset( $field->id ) )
        {
            // Insert with existing Field id and given Content version number
            $this->contentGateway->insertExistingField(
                $content,
                $field,
                $storageValue
            );
        }
        else
        {
            // Insert with creating new Field id and given Content version number
            $field->id = $this->contentGateway->insertNewField(
                $content,
                $field,
                $storageValue
            );
        }

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

            $this->contentGateway->updateField(
                $field,
                $storageValue
            );
        }

        return $field->id;
    }

    /**
     * Creates new Field value object, setting given parameters and default value
     * for a field definition the action is constructed for.
     *
     * @param null|int $id
     * @param int $versionNo
     * @param string $languageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field
     */
    protected function createField( $id, $versionNo, $languageCode )
    {
        $field = new Field();

        $field->id = $id;
        $field->fieldDefinitionId = $this->fieldDefinition->id;
        $field->type = $this->fieldDefinition->fieldType;
        $field->value = clone $this->fieldDefinition->defaultValue;
        $field->versionNo = $versionNo;
        $field->languageCode = $languageCode;

        return $field;
    }
}
