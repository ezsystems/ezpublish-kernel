<?php
/**
 * File containing the ContentTypeGroup parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Client\Values;

/**
 * Parser for ContentTypeGroup
 */
class ContentTypeGroup extends Parser
{
    /**
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct( ParserTools $parserTools )
    {
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $creatorId = $this->parserTools->parseObjectElement( $data['Creator'], $parsingDispatcher );
        $modifierId = $this->parserTools->parseObjectElement( $data['Modifier'], $parsingDispatcher );

        return new Values\ContentType\ContentTypeGroup(
            array(
                'id' => $data['_href'],
                'identifier' => $data['identifier'],
                'creationDate' => new \DateTime( $data['created'] ),
                'modificationDate' => new \DateTime( $data['modified'] ),
                'creatorId' => $creatorId,
                'modifierId' => $modifierId,
            )
        );
    }
}
