<?php
/**
 * File containing the Location parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Input\ParserTools;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\Core\Repository\Values;

/**
 * Parser for Location
 */
class Location extends Parser
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
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     * @todo Error handling
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $modifiedSubLocationDate = new \DateTime();
        $modifiedSubLocationDate->setTimestamp( strtotime( $data['subLocationModificationDate'] ) );

        $content = $parsingDispatcher->parse( $data['Content'], 'Content' );

        return new Values\Content\Location(
            array(
                'contentInfo' => $content instanceof APIContent ? $content->getVersionInfo()->getContentInfo() : null,
                'id' => $data['_href'],
                'priority' => (int) $data['priority'],
                'hidden' => $data['hidden'] === 'true' ? true : false,
                'invisible' => $data['invisible'] === 'true' ? true : false,
                'remoteId' => $data['remoteId'],
                'parentLocationId' => $data['ParentLocation']['_href'],
                'pathString' => $data['pathString'],
                'modifiedSubLocationDate' => $modifiedSubLocationDate,
                'depth' => (int) $data['depth'],
                'sortField' => $this->parserTools->parseDefaultSortField( $data['sortField'] ),
                'sortOrder' => $this->parserTools->parseDefaultSortOrder( $data['sortOrder'] ),
                'childCount' => (int) $data['childCount']
            )
        );
    }
}
