<?php
/**
 * File containing the ContentTypeGroupInput parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\ContentTypeService;
use DateTime;

/**
 * Parser for ContentTypeGroupInput
 */
class ContentTypeGroupInput extends Base
{
    /**
     * ContentType service
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct( UrlHandler $urlHandler, ContentTypeService $contentTypeService, ParserTools $parserTools )
    {
        parent::__construct( $urlHandler );
        $this->contentTypeService = $contentTypeService;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        // Since ContentTypeGroupInput is used both for creating and updating ContentTypeGroup and identifier is not
        // required when updating ContentTypeGroup, we need to rely on PAPI to throw the exception on missing
        // identifier when creating a ContentTypeGroup
        // @todo Bring in line with XSD which says that identifier is required always

        $contentTypeGroupIdentifier = null;
        if ( array_key_exists( 'identifier', $data ) )
        {
            $contentTypeGroupIdentifier = $data['identifier'];
        }

        $contentTypeGroupCreateStruct = $this->contentTypeService->newContentTypeGroupCreateStruct( $contentTypeGroupIdentifier );

        if ( array_key_exists( 'modificationDate', $data ) )
        {
            $contentTypeGroupCreateStruct->creationDate = new DateTime( $data['modificationDate'] );
        }

        // @todo mainLanguageCode, names, descriptions?

        if ( array_key_exists( 'User', $data ) && is_array( $data['User'] ) )
        {
            if ( !array_key_exists( '_href', $data['User'] ) )
            {
                throw new Exceptions\Parser( "Missing '_href' attribute for User element in ContentTypeGroupInput." );
            }

            $userValues = $this->urlHandler->parse( 'user', $data['User']['_href'] );
            $contentTypeGroupCreateStruct->creatorId = $userValues['user'];
        }

        return $contentTypeGroupCreateStruct;
    }
}
