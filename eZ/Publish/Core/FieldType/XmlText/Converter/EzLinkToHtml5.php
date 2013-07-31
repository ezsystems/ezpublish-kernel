<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter\EzLinkToHtml5 class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\FieldType\XmlText\Converter;
use eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter;
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
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter
     */
    protected $urlAliasRouter;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct( LocationService $locationService, ContentService $contentService, UrlAliasRouter $urlAliasRouter, LoggerInterface $logger = null )
    {
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->urlAliasRouter = $urlAliasRouter;
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
                    $contentInfo = $this->contentService->loadContentInfo( $id );
                    $location = $this->locationService->loadLocation( $contentInfo->mainLocationId );
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
                $link->setAttribute( 'xlink:href', $this->urlAliasRouter->generate( $location ) . $fragment );
            }
        }

        return $document;
    }
}
