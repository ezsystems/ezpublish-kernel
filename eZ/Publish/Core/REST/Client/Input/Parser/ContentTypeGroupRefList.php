<?php
/**
 * File containing the ContentTypeGroupRefList parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Client\ContentTypeService;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

use eZ\Publish\Core\REST\Client\Values;

/**
 * Parser for ContentTypeGroupRefList
 */
class ContentTypeGroupRefList extends Parser
{
    /**
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * @var \eZ\Publish\Core\REST\Client\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @param ParserTools $parserTools
     * @param \eZ\Publish\Core\REST\Client\ContentTypeService $contentTypeService
     */
    public function __construct( ParserTools $parserTools, ContentTypeService $contentTypeService )
    {
        $this->parserTools = $parserTools;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Client\Values\ContentTypeGroupRefList
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $contentTypeGroupReferences = array();
        foreach ( $data['ContentTypeGroupRef'] as $groupData )
        {
            $contentTypeGroupReferences[] = $groupData["_href"];
        }

        return new Values\ContentTypeGroupRefList(
            $this->contentTypeService,
            $data["_href"],
            $contentTypeGroupReferences
        );
    }
}
