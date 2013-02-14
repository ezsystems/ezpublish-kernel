<?php
/**
 * File containing the ContentInfo parser class
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
 * Parser for ContentInfo
 */
class ContentInfo extends Parser
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
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
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
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     * @todo Error handling
     * @todo What about missing properties? Set them here, using the service to
     *       load? Or better set them in the service, since loading is really
     *       unsuitable here?
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $contentTypeId = $this->parserTools->parseObjectElement( $data['ContentType'], $parsingDispatcher );
        $ownerId = $this->parserTools->parseObjectElement( $data['Owner'], $parsingDispatcher );
        $mainLocationId = $this->parserTools->parseObjectElement( $data['MainLocation'], $parsingDispatcher );
        $sectionId = $this->parserTools->parseObjectElement( $data['Section'], $parsingDispatcher );

        $locationListReference = $this->parserTools->parseObjectElement( $data['Locations'], $parsingDispatcher );
        $versionListReference = $this->parserTools->parseObjectElement( $data['Versions'], $parsingDispatcher );
        $currentVersionReference = $this->parserTools->parseObjectElement( $data['CurrentVersion'], $parsingDispatcher );

        if ( isset( $data['CurrentVersion']['Version'] ) )
        {
            $this->parserTools->parseObjectElement( $data['CurrentVersion']['Version'], $parsingDispatcher );
        }

        return new Values\RestContentInfo(
            array(
                'id'   => $data['_href'],
                'name' => $data['Name'],
                'contentTypeId' => $contentTypeId,
                'ownerId' => $ownerId,
                'modificationDate' => new \DateTime( $data['lastModificationDate'] ),

                'publishedDate' => ( $publishedDate = ( !empty( $data['publishedDate'] )
                    ? new \DateTime( $data['publishedDate'] )
                    : null ) ),

                'published' => ( $publishedDate !== null ),
                'alwaysAvailable' => ( strtolower( $data['alwaysAvailable'] ) === 'true' ),
                'remoteId' => $data['_remoteId'],
                'mainLanguageCode' => $data['mainLanguageCode'],
                'mainLocationId' => $mainLocationId,
                'sectionId' => $sectionId,

                'versionListReference' => $versionListReference,
                'locationListReference' => $locationListReference,
                'currentVersionReference' => $currentVersionReference,
            )
        );
    }
}
