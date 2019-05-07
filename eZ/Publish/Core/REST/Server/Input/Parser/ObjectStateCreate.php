<?php

/**
 * File containing the ObjectStateCreate parser class.
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
 * Parser for ObjectStateCreate.
 */
class ObjectStateCreate extends BaseParser
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
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('identifier', $data)) {
            throw new Exceptions\Parser("Missing 'identifier' attribute for ObjectStateCreate.");
        }

        $objectStateCreateStruct = $this->objectStateService->newObjectStateCreateStruct($data['identifier']);

        if (!array_key_exists('priority', $data)) {
            throw new Exceptions\Parser("Missing 'priority' attribute for ObjectStateCreate.");
        }

        $objectStateCreateStruct->priority = (int)$data['priority'];

        if (!array_key_exists('defaultLanguageCode', $data)) {
            throw new Exceptions\Parser("Missing 'defaultLanguageCode' attribute for ObjectStateCreate.");
        }

        $objectStateCreateStruct->defaultLanguageCode = $data['defaultLanguageCode'];

        if (!array_key_exists('names', $data) || !is_array($data['names'])) {
            throw new Exceptions\Parser("Missing or invalid 'names' element for ObjectStateCreate.");
        }

        if (!array_key_exists('value', $data['names']) || !is_array($data['names']['value'])) {
            throw new Exceptions\Parser("Missing or invalid 'names' element for ObjectStateCreate.");
        }

        $objectStateCreateStruct->names = $this->parserTools->parseTranslatableList($data['names']);

        // @todo XSD says that descriptions field is mandatory. Does that make sense?
        if (array_key_exists('descriptions', $data) && is_array($data['descriptions'])) {
            $objectStateCreateStruct->descriptions = $this->parserTools->parseTranslatableList($data['descriptions']);
        }

        return $objectStateCreateStruct;
    }
}
