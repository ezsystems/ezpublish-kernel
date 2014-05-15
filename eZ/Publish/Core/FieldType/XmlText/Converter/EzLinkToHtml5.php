<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter\EzLinkToHtml5 class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
     * Converts internal links (eznode:// and ezobject://) to URLs.
     *
     * @param \DOMDocument $xmlDoc
     *
     * @return string|null
     */
    public function convert( DOMDocument $xmlDoc )
    {
        foreach ( $xmlDoc->getElementsByTagName( "link" ) as $link )
        {
            $location = null;

            if ( $link->hasAttribute( 'object_id' ) )
            {
                try
                {
                    $contentInfo = $this->contentService->loadContentInfo( $link->getAttribute( 'object_id' ) );
                    $location = $this->locationService->loadLocation( $contentInfo->mainLocationId );
                }
                catch ( APINotFoundException $e )
                {
                    if ( $this->logger )
                    {
                        $this->logger->warning(
                            "While generating links for xmltext, could not locate " .
                            "Content object with ID " . $link->getAttribute( 'object_id' )
                        );
                    }
                }
                catch ( APIUnauthorizedException $e )
                {
                    if ( $this->logger )
                    {
                        $this->logger->notice(
                            "While generating links for xmltext, unauthorized to load " .
                            "Content object with ID " . $link->getAttribute( 'object_id' )
                        );
                    }
                }
            }

            if ( $link->hasAttribute( 'node_id' ) )
            {
                try
                {
                    $location = $this->locationService->loadLocation( $link->getAttribute( 'node_id' ) );
                }
                catch ( APINotFoundException $e )
                {
                    if ( $this->logger )
                    {
                        $this->logger->warning(
                            "While generating links for xmltext, could not locate " .
                            "Location with ID " . $link->getAttribute( 'node_id' )
                        );
                    }
                }
                catch ( APIUnauthorizedException $e )
                {
                    if ( $this->logger )
                    {
                        $this->logger->notice(
                            "While generating links for xmltext, unauthorized to load " .
                            "Location with ID " . $link->getAttribute( 'node_id' )
                        );
                    }
                }
            }

            if ( $location !== null )
            {
                $link->setAttribute( 'url', $this->urlAliasRouter->generate( $location ) );
            }

            if ( $link->hasAttribute( 'anchor_name' ) )
            {
                $link->setAttribute( 'url', $link->getAttribute( 'url' ) . "#" . $link->getAttribute( 'anchor_name' ) );
            }
        }
    }
}
