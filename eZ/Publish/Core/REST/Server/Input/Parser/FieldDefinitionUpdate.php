<?php

/**
 * File containing the FieldDefinitionUpdate parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Parser for FieldDefinitionUpdate.
 */
class FieldDefinitionUpdate extends BaseParser
{
    /**
     * ContentType service.
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * FieldType parser.
     *
     * @var \eZ\Publish\Core\REST\Common\Input\FieldTypeParser
     */
    protected $fieldTypeParser;

    /**
     * Parser tools.
     *
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct(ContentTypeService $contentTypeService, FieldTypeParser $fieldTypeParser, ParserTools $parserTools)
    {
        $this->contentTypeService = $contentTypeService;
        $this->fieldTypeParser = $fieldTypeParser;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $fieldDefinitionUpdate = $this->contentTypeService->newFieldDefinitionUpdateStruct();

        if (array_key_exists('identifier', $data)) {
            $fieldDefinitionUpdate->identifier = $data['identifier'];
        }

        // @todo XSD says that descriptions is mandatory, but field definition can be updated without it
        if (array_key_exists('names', $data)) {
            if (!is_array($data['names']) || !array_key_exists('value', $data['names']) || !is_array($data['names']['value'])) {
                throw new Exceptions\Parser("Invalid 'names' element for FieldDefinitionUpdate.");
            }

            $fieldDefinitionUpdate->names = $this->parserTools->parseTranslatableList($data['names']);
        }

        // @todo XSD says that descriptions is mandatory, but field definition can be updated without it
        if (array_key_exists('descriptions', $data)) {
            if (!is_array($data['descriptions']) || !array_key_exists('value', $data['descriptions']) || !is_array($data['descriptions']['value'])) {
                throw new Exceptions\Parser("Invalid 'descriptions' element for FieldDefinitionUpdate.");
            }

            $fieldDefinitionUpdate->descriptions = $this->parserTools->parseTranslatableList($data['descriptions']);
        }

        // @todo XSD says that fieldGroup is mandatory, but field definition can be updated without it
        if (array_key_exists('fieldGroup', $data)) {
            $fieldDefinitionUpdate->fieldGroup = $data['fieldGroup'];
        }

        // @todo XSD says that position is mandatory, but field definition can be updated without it
        if (array_key_exists('position', $data)) {
            $fieldDefinitionUpdate->position = (int)$data['position'];
        }

        // @todo XSD says that isTranslatable is mandatory, but field definition can be updated without it
        if (array_key_exists('isTranslatable', $data)) {
            $fieldDefinitionUpdate->isTranslatable = $this->parserTools->parseBooleanValue($data['isTranslatable']);
        }

        // @todo XSD says that isRequired is mandatory, but field definition can be updated without it
        if (array_key_exists('isRequired', $data)) {
            $fieldDefinitionUpdate->isRequired = $this->parserTools->parseBooleanValue($data['isRequired']);
        }

        // @todo XSD says that isInfoCollector is mandatory, but field definition can be updated without it
        if (array_key_exists('isInfoCollector', $data)) {
            $fieldDefinitionUpdate->isInfoCollector = $this->parserTools->parseBooleanValue($data['isInfoCollector']);
        }

        // @todo XSD says that isSearchable is mandatory, but field definition can be updated without it
        if (array_key_exists('isSearchable', $data)) {
            $fieldDefinitionUpdate->isSearchable = $this->parserTools->parseBooleanValue($data['isSearchable']);
        }

        $fieldDefinition = $this->getFieldDefinition($data);

        // @todo XSD says that defaultValue is mandatory, but content type can be created without it
        if (array_key_exists('defaultValue', $data)) {
            $fieldDefinitionUpdate->defaultValue = $this->fieldTypeParser->parseValue(
                $fieldDefinition->fieldTypeIdentifier,
                $data['defaultValue']
            );
        }

        if (array_key_exists('validatorConfiguration', $data)) {
            $fieldDefinitionUpdate->validatorConfiguration = $this->fieldTypeParser->parseValidatorConfiguration(
                $fieldDefinition->fieldTypeIdentifier,
                $data['validatorConfiguration']
            );
        }

        if (array_key_exists('fieldSettings', $data)) {
            $fieldDefinitionUpdate->fieldSettings = $this->fieldTypeParser->parseFieldSettings(
                $fieldDefinition->fieldTypeIdentifier,
                $data['fieldSettings']
            );
        }

        return $fieldDefinitionUpdate;
    }

    /**
     * Returns field definition by 'typeFieldDefinitionDraft' pattern URL.
     *
     * Assumes given $data array has '__url' element set.
     *
     * @todo depends on temporary solution to give parser access to the URL
     *
     * @see \eZ\Publish\Core\REST\Server\Controller\ContentType::updateFieldDefinition
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param array $data
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    protected function getFieldDefinition(array $data)
    {
        $contentTypeId = $this->requestParser->parseHref($data['__url'], 'contentTypeId');
        $fieldDefinitionId = $this->requestParser->parseHref($data['__url'], 'fieldDefinitionId');

        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        foreach ($contentTypeDraft->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->id == $fieldDefinitionId) {
                return $fieldDefinition;
            }
        }
        throw new Exceptions\NotFoundException("Field definition not found: '{$data['__url']}'.");
    }
}
