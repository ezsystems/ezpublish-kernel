<?php

/**
 * File containing the ObjectStateGroupUpdate parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use EzSystems\EzPlatformRestCommon\Input\BaseParser;
use EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher;
use EzSystems\EzPlatformRestCommon\Input\ParserTools;
use EzSystems\EzPlatformRestCommon\Exceptions;
use eZ\Publish\API\Repository\ObjectStateService;

/**
 * Parser for ObjectStateGroupUpdate.
 */
class ObjectStateGroupUpdate extends BaseParser
{
    /**
     * Object state service.
     *
     * @var \eZ\Publish\API\Repository\ObjectStateService
     */
    protected $objectStateService;

    /**
     * @var \EzSystems\EzPlatformRestCommon\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\ObjectStateService $objectStateService
     * @param \EzSystems\EzPlatformRestCommon\Input\ParserTools $parserTools
     */
    public function __construct(ObjectStateService $objectStateService, ParserTools $parserTools)
    {
        $this->objectStateService = $objectStateService;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $objectStateGroupUpdateStruct = $this->objectStateService->newObjectStateGroupUpdateStruct();

        if (array_key_exists('identifier', $data)) {
            $objectStateGroupUpdateStruct->identifier = $data['identifier'];
        }

        if (array_key_exists('defaultLanguageCode', $data)) {
            $objectStateGroupUpdateStruct->defaultLanguageCode = $data['defaultLanguageCode'];
        }

        if (array_key_exists('names', $data)) {
            if (!is_array($data['names'])) {
                throw new Exceptions\Parser("Missing or invalid 'names' element for ObjectStateGroupUpdate.");
            }

            if (!array_key_exists('value', $data['names']) || !is_array($data['names']['value'])) {
                throw new Exceptions\Parser("Missing or invalid 'names' element for ObjectStateGroupUpdate.");
            }

            $objectStateGroupUpdateStruct->names = $this->parserTools->parseTranslatableList($data['names']);
        }

        if (array_key_exists('descriptions', $data) && is_array($data['descriptions'])) {
            $objectStateGroupUpdateStruct->descriptions = $this->parserTools->parseTranslatableList($data['descriptions']);
        }

        return $objectStateGroupUpdateStruct;
    }
}
