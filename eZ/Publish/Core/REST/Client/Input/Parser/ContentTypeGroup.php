<?php

/**
 * File containing the ContentTypeGroup parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use EzSystems\EzPlatformRestCommon\Input\BaseParser;
use EzSystems\EzPlatformRestCommon\Input\ParserTools;
use EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Client\Values;

/**
 * Parser for ContentTypeGroup.
 */
class ContentTypeGroup extends BaseParser
{
    /**
     * @var \EzSystems\EzPlatformRestCommon\Input\ParserTools
     */
    protected $parserTools;

    /**
     * @param \EzSystems\EzPlatformRestCommon\Input\ParserTools $parserTools
     */
    public function __construct(ParserTools $parserTools)
    {
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $creatorId = $this->parserTools->parseObjectElement($data['Creator'], $parsingDispatcher);
        $modifierId = $this->parserTools->parseObjectElement($data['Modifier'], $parsingDispatcher);

        return new Values\ContentType\ContentTypeGroup(
            array(
                'id' => $data['_href'],
                'identifier' => $data['identifier'],
                'creationDate' => new \DateTime($data['created']),
                'modificationDate' => new \DateTime($data['modified']),
                'creatorId' => $creatorId,
                'modifierId' => $modifierId,
            )
        );
    }
}
