<?php
/**
 * File containing the ContentTypeId Criterion parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;
use eZ\Publish\Core\REST\Server\Input\Parser\Base;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId as ContentTypeIdCriterion;

/**
 * Parser for ViewInput
 */
class ContentTypeId extends Base
{
    public function __construct( UrlHandler $urlHandler )
    {
        parent::__construct( $urlHandler );
    }

    /**
     * Parses input structure to a Criterion object
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( "ContentTypeIdCriterion", $data ) )
        {
            throw new Exceptions\Parser( "Invalid <ContentTypeIdCriterion> format" );
        }
        return new ContentTypeIdCriterion( $data["ContentTypeIdCriterion"] );
    }
}
