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
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

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
     * Content Type Handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler
     */
    public $typeHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    public $languageHandler;

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
     * Hash of SPI FieldTypes or callable callbacks to generate one.
     *
     * @var array
     */
    protected $fieldTypes;

    /**
     * Creates a new Field Handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Mapper $mapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler $storageHandler
     * @param array $fieldTypes Hash of SPI FieldTypes or callable callbacks to generate one.
     */
    public function __construct(
        Gateway $contentGateway,
        Mapper $mapper,
        StorageHandler $storageHandler,
        array $fieldTypes )
    {
        $this->contentGateway = $contentGateway;
        $this->mapper = $mapper;
        $this->storageHandler = $storageHandler;
        $this->fieldTypes = $fieldTypes;
    }

    /**
     * Instantiates a FieldType\Type object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $type not properly setup
     *         with settings injected to service
     *
     * @param $identifier
     *
     * @return \eZ\Publish\SPI\FieldType\FieldType
     */
    protected function buildFieldType( $identifier )
    {
        if ( !isset( $this->fieldTypes[$identifier] ) )
        {
            throw new NotFoundException(
                "FieldType",
                "Provided \$identifier is unknown: '{$identifier}', has: " . var_export( array_keys( $this->fieldTypes ), true )
            );
        }

        if ( $this->fieldTypes[$identifier] instanceof \eZ\Publish\SPI\FieldType\FieldType )
        {
            return $this->fieldTypes[$identifier];
        }
        else if ( !is_callable( $this->fieldTypes[$identifier] ) )
        {
            throw new InvalidArgumentException( "\$settings[$identifier]", 'must be instance of SPI\\FieldType\\FieldType or callback to generate it' );
        }

        /** @var $closure \Closure */
        $closure = $this->fieldTypes[$identifier];
        return $closure();
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
        $this->createCompleteFields( $content );

        foreach ( $content->fields as $field )
        {
            $this->createNewField( $field, $content );
        }
    }

    /**
     * Adds missing fields to the given $content field collection.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return void
     */
    protected function createCompleteFields( Content $content )
    {
        $languageCodes = array();
        $fields = $this->getFieldMap( $content->fields, $languageCodes );
        $contentType = $this->typeHandler->load( $content->versionInfo->contentInfo->contentTypeId );

        foreach ( $contentType->fieldDefinitions as $fieldDefinition )
        {
            foreach ( array_keys( $languageCodes ) as $languageCode )
            {
                if ( isset( $fields[$fieldDefinition->id][$languageCode] ) )
                {
                    continue;
                }

                if ( $fieldDefinition->isTranslatable
                    || !isset( $fields[$fieldDefinition->id][$content->versionInfo->contentInfo->mainLanguageCode] ) )
                {
                    $content->fields[] = $this->getEmptyField( $fieldDefinition, $languageCode );
                }
                else
                {
                    $field = clone $fields[$fieldDefinition->id][$content->versionInfo->contentInfo->mainLanguageCode];
                    $field->id = null;
                    $field->languageCode = $languageCode;
                    $content->fields[] = $field;
                }
            }
        }
    }

    /**
     * Returns empty Field object for given field definition and language code.
     *
     * Uses FieldType to create empty field value.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param string $languageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field
     */
    protected function getEmptyField( FieldDefinition $fieldDefinition, $languageCode )
    {
        $fieldType = $this->buildFieldType( $fieldDefinition->fieldType );
        return new Field(
            array(
                "fieldDefinitionId" => $fieldDefinition->id,
                "type" => $fieldDefinition->fieldType,
                "value" => $fieldType->toPersistenceValue( $fieldType->getEmptyValue() ),
                "languageCode" => $languageCode
            )
        );
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
     * @return void
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
     * Updates an existing field in the database.
     *
     * Used by self::createNewFields() and self::updateFields()
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return void
     */
    protected function updateField( Field $field, Content $content )
    {
        $this->contentGateway->updateField(
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
    public function updateFields( Content $content, UpdateStruct $updateStruct )
    {
        $languageCodes = $existingLanguageCodes = $this->getLanguageCodes( $content->versionInfo->languageIds );
        $contentFieldMap = $this->getFieldMap( $content->fields );
        $updateFieldMap = $this->getFieldMap( $updateStruct->fields, $languageCodes );
        $contentType = $this->typeHandler->load( $content->versionInfo->contentInfo->contentTypeId );

        foreach ( $contentType->fieldDefinitions as $fieldDefinition )
        {
            foreach ( array_keys( $languageCodes ) as $languageCode )
            {
                if ( isset( $updateFieldMap[$fieldDefinition->id][$languageCode] ) )
                {
                    $field = $updateFieldMap[$fieldDefinition->id][$languageCode];
                    $field->versionNo = $content->versionInfo->versionNo;
                    if ( isset( $field->id ) )
                    {
                        $this->updateField( $field, $content );
                    }
                    else
                    {
                        $this->createNewField( $field, $content );
                    }
                }
                // If field is not set for new language
                else if ( !isset( $existingLanguageCodes[$languageCode] ) )
                {
                    if ( $fieldDefinition->isTranslatable )
                    {
                        // Use empty value
                        $field = $this->getEmptyField( $fieldDefinition, $languageCode );
                    }
                    else
                    {
                        // Use value from main language code
                        $field = $contentFieldMap[$fieldDefinition->id][$content->versionInfo->contentInfo->mainLanguageCode];
                        $field->id = null;
                        $field->languageCode = $languageCode;
                    }

                    $this->createNewField( $field, $content );
                }
            }
        }
    }

    /**
     * For given $languageIds returns array with language codes as keys.
     *
     * @param array $languageIds
     *
     * @return array
     */
    protected function getLanguageCodes( array $languageIds )
    {
        $languageCodes = array();
        foreach ( $languageIds as $languageId )
        {
            $languageCodes[$this->languageHandler->load( $languageId )->languageCode] = true;
        }

        return $languageCodes;
    }

    /**
     * Returns given $fields structured in hash array with field definition ids and language codes as keys.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $fields
     * @param array $languageCodes
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field[][]
     */
    protected function getFieldMap( array $fields, &$languageCodes = null )
    {
        $fieldMap = array();
        foreach ( $fields as $field )
        {
            if ( isset( $languageCodes ) )
            {
                $languageCodes[$field->languageCode] = true;
            }
            $fieldMap[$field->fieldDefinitionId][$field->languageCode] = $field;
        }

        return $fieldMap;
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
