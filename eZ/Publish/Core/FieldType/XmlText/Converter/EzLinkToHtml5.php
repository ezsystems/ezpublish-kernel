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
                    $content = $this->contentService->loadContent( $link->getAttribute( 'object_id' ) );
                    $location = $this->locationService->loadLocation( $content->contentInfo->mainLocationId );
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
                $urlAlias = $this->urlAliasService->reverseLookup( $location );
                $link->setAttribute( 'url', $urlAlias->path );
            }
        }
    }
}
