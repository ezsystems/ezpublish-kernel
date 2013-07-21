<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter\EzLinkToHtml5 class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter;
use eZ\Publish\API\Repository\Repository;
use Psr\Log\LoggerInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException as APIUnauthorizedException;

use DOMDocument;

class EzLinkToHtml5 implements Converter
{
    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * @var \eZ\Publish\API\Repository\URLAliasService
     */
    protected $urlAliasService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct( Repository $repository, LoggerInterface $logger = null )
    {
        $this->locationService = $repository->getLocationService();
        $this->contentService = $repository->getContentService();
        $this->urlAliasService = $repository->getURLAliasService();
        $this->logger = $logger;
    }

    /**
     * Converts internal links (ezcontent:// and ezlocation://) to URLs.
     *
     * @param \DOMDocument $document
     *
     * @return \DOMDocument
     */
    public function convert( DOMDocument $document )
    {
        $document = clone $document;
        $xpath = new \DOMXPath( $document );
        $xpath->registerNamespace( "docbook", "http://docbook.org/ns/docbook" );
        $xpathExpression = "//docbook:link[starts-with( @xlink:href, 'ezlocation://' ) or starts-with( @xlink:href, 'ezcontent://' )]";

        /** @var \DOMElement $link */
        foreach ( $xpath->query( $xpathExpression ) as $link )
        {
            $location = null;
            preg_match( "~^(.+)://([^#]*)?(#.*|\\s*)?$~", $link->getAttribute( "xlink:href" ), $matches );
            list( , $protocol, $id, $fragment ) = $matches;

            if ( $protocol === "ezcontent" )
            {
                try
                {
                    $content = $this->contentService->loadContent( $id );
                    $location = $this->locationService->loadLocation( $content->contentInfo->mainLocationId );
                }
                catch ( APINotFoundException $e )
                {
                    if ( $this->logger )
                    {
                        $this->logger->warning(
                            "While generating links for xmltext, could not locate " .
                            "Content object with ID " . $id
                        );
                    }
                }
                catch ( APIUnauthorizedException $e )
                {
                    if ( $this->logger )
                    {
                        $this->logger->notice(
                            "While generating links for xmltext, unauthorized to load " .
                            "Content object with ID " . $id
                        );
                    }
                }
            }

            if ( $protocol === "ezlocation" )
            {
                try
                {
                    $location = $this->locationService->loadLocation( $id );
                }
                catch ( APINotFoundException $e )
                {
                    if ( $this->logger )
                    {
                        $this->logger->warning(
                            "While generating links for xmltext, could not locate " .
                            "Location with ID " . $id
                        );
                    }
                }
                catch ( APIUnauthorizedException $e )
                {
                    if ( $this->logger )
                    {
                        $this->logger->notice(
                            "While generating links for xmltext, unauthorized to load " .
                            "Location with ID " . $id
                        );
                    }
                }
            }

            if ( $location !== null )
            {
                $urlAlias = $this->urlAliasService->reverseLookup( $location );
                $link->setAttribute( 'xlink:href', $urlAlias->path . $fragment );
            }
        }

        return $document;
    }
}
