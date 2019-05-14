<?php

/**
 * File containing the ObjectStateGroupUpdate parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use EzSystems\EzPlatformRest\Input\BaseParser;
use EzSystems\EzPlatformRest\Input\ParsingDispatcher;
use EzSystems\EzPlatformRest\Input\ParserTools;
use EzSystems\EzPlatformRest\Exceptions;
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
     * @var \EzSystems\EzPlatformRest\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\ObjectStateService $objectStateService
     * @param \EzSystems\EzPlatformRest\Input\ParserTools $parserTools
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
     * @param \EzSystems\EzPlatformRest\Input\ParsingDispatcher $parsingDispatcher
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
