<?php
/**
 * File containing the Input FieldTypeParser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Input;

use eZ\Publish\Core\REST\Common\FieldTypeProcessorRegistry;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\FieldTypeService;

class FieldTypeParser
{
    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    protected $fieldTypeService;

    /**
     * @var \eZ\Publish\Core\REST\Common\FieldTypeProcessorRegistry
     */
    protected $fieldTypeProcessorRegistry;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\API\Repository\FieldTypeService $fieldTypeService
     * @param \eZ\Publish\Core\REST\Common\FieldTypeProcessorRegistry $fieldTypeProcessorRegistry
     */
    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        FieldTypeService $fieldTypeService,
        FieldTypeProcessorRegistry $fieldTypeProcessorRegistry )
    {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->fieldTypeService = $fieldTypeService;
        $this->fieldTypeProcessorRegistry = $fieldTypeProcessorRegistry;
    }

    /**
     * Parses the given $value for the field $fieldDefIdentifier in the content
     * identified by $contentInfoId
     *
     * @param string $contentInfoId
     * @param string $fieldDefIdentifier
     * @param mixed $value
     *
     * @return mixed
     */
    public function parseFieldValue( $contentInfoId, $fieldDefIdentifier, $value )
    {
        $contentInfo = $this->contentService->loadContentInfo( $contentInfoId );
        $contentType = $this->contentTypeService->loadContentType( $contentInfo->contentTypeId );

        $fieldDefinition = $contentType->getFieldDefinition( $fieldDefIdentifier );

        return $this->parseValue( $fieldDefinition->fieldTypeIdentifier, $value );
    }

    /**
     * Parses the given $value using the FieldType identified by
     * $fieldTypeIdentifier
     *
     * @param mixed $fieldTypeIdentifier
     * @param mixed $value
     *
     * @return mixed
     */
    public function parseValue( $fieldTypeIdentifier, $value )
    {
        $fieldType = $this->fieldTypeService->getFieldType( $fieldTypeIdentifier );

        if ( $this->fieldTypeProcessorRegistry->hasProcessor( $fieldTypeIdentifier ) )
        {
            $fieldTypeProcessor = $this->fieldTypeProcessorRegistry->getProcessor( $fieldTypeIdentifier );
            $value = $fieldTypeProcessor->preProcessHash( $value );
        }

        return $fieldType->fromHash( $value );
    }

    /**
     * Parses the given $settingsHash using the FieldType identified by
     * $fieldTypeIdentifier
     *
     * @param string $fieldTypeIdentifier
     * @param mixed $settingsHash
     *
     * @return mixed
     */
    public function parseFieldSettings( $fieldTypeIdentifier, $settingsHash )
    {
        $fieldType = $this->fieldTypeService->getFieldType( $fieldTypeIdentifier );
        return $fieldType->fieldSettingsFromHash( $settingsHash );
    }

    /**
     * Parses the given $configurationHash using the FieldType identified by
     * $fieldTypeIdentifier
     *
     * @param string $fieldTypeIdentifier
     * @param mixed $configurationHash
     *
     * @return mixed
     */
    public function parseValidatorConfiguration( $fieldTypeIdentifier, $configurationHash )
    {
        $fieldType = $this->fieldTypeService->getFieldType( $fieldTypeIdentifier );
        return $fieldType->validatorConfigurationFromHash( $configurationHash );
    }
}
