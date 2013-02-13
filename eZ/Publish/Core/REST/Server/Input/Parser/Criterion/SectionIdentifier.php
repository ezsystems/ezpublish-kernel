<?php
/**
 * File containing the SectionIdentifier Criterion parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\Core\REST\Server\Input\Parser\Base;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\SectionId as SectionIdCriterion;

/**
 * Parser for SectionIdentifier Criterion
 */
class SectionIdentifier extends Base
{
    /**
     * Section service
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $sectionService;

    public function __construct( UrlHandler $urlHandler, SectionService $sectionService )
    {
        $this->sectionService = $sectionService;
        parent::__construct( $urlHandler );
    }

    /**
     * Parses input structure to a SectionIdentifier Criterion object
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\SectionId
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( "SectionIdentifierCriterion", $data ) )
        {
            throw new Exceptions\Parser( "Invalid <SectionIdentifierCriterion> format" );
        }
        $section = $this->sectionService->loadSectionByIdentifier( $data["SectionIdentifierCriterion"] );
        return new SectionIdCriterion( $section->id );
    }
}
