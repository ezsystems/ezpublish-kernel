<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter\EmbedToHtml5 class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter;
use eZ\Publish\API\Repository\Repository;
use DOMDocument;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

/**
 * Converts embedded elements from internal XmlText representation to HTML5
 */
class EmbedToHtml5 implements Converter
{
    /**
     * List of disallowed attributes
     * @var array
     */
    protected $excludedAttributes = array();

    /**
     * @var \Symfony\Component\HttpKernel\Fragment\FragmentHandler
     */
    protected $fragmentHandler;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        FragmentHandler $fragmentHandler,
        Repository $repository,
        array $excludedAttributes,
        LoggerInterface $logger = null
    )
    {
        $this->fragmentHandler = $fragmentHandler;
        $this->repository = $repository;
        $this->excludedAttributes = array_fill_keys( $excludedAttributes, true );
        $this->logger = $logger;
    }

    /**
     * Process embed tags for a single tag type (embed or embed-inline)
     * @param \DOMDocument $xmlDoc
     * @param $tagName string name of the tag to extract
     */
    protected function processTag( DOMDocument $xmlDoc, $tagName )
    {
        /** @var $embed \DOMElement */
        foreach ( $xmlDoc->getElementsByTagName( $tagName ) as $embed )
        {
            if ( !$view = $embed->getAttribute( "view" ) )
            {
                $view = $tagName;
            }

            $embedContent = null;
            $parameters = array(
                "noLayout" => true,
                "objectParameters" => array()
            );

            foreach ( $embed->attributes as $attribute )
            {
                // We only consider tags in the custom namespace, and skip disallowed names
                if ( !isset( $this->excludedAttributes[$attribute->localName] ) )
                {
                    $parameters["objectParameters"][$attribute->localName] = $attribute->nodeValue;
                }
            }

            if ( $contentId = $embed->getAttribute( "object_id" ) )
            {
                try
                {
                    /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
                    $content = $this->repository->sudo(
                        function ( Repository $repository ) use ( $contentId )
                        {
                            return $repository->getContentService()->loadContent( $contentId );
                        }
                    );

                    if (
                        !$this->repository->canUser( 'content', 'read', $content )
                        && !$this->repository->canUser( 'content', 'view_embed', $content )
                    )
                    {
                        throw new UnauthorizedException( 'content', 'read', array( 'contentId' => $contentId ) );
                    }

                    // Check published status of the Content
                    if (
                        $content->getVersionInfo()->status !== APIVersionInfo::STATUS_PUBLISHED
                        && !$this->repository->canUser( 'content', 'versionread', $content )
                    )
                    {
                        throw new UnauthorizedException( 'content', 'versionread', array( 'contentId' => $contentId ) );
                    }

                    $controllerReference = new ControllerReference( 'ez_content:viewContent', array( 'contentId' => $content->id, 'viewType' => $view, 'params' => $parameters ) );
                    $embedContent = $this->fragmentHandler->render( $controllerReference );
                }
                catch ( APINotFoundException $e )
                {
                    if ( $this->logger )
                    {
                        $this->logger->error(
                            "While generating embed for xmltext, could not locate " .
                            "Content object with ID " . $contentId
                        );
                    }
                }
            }
            else if ( $locationId = $embed->getAttribute( "node_id" ) )
            {
                try
                {
                    /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
                    $location = $this->repository->sudo(
                        function ( Repository $repository ) use ( $locationId )
                        {
                            return $repository->getLocationService()->loadLocation( $locationId );
                        }
                    );

                    if (
                        !$this->repository->canUser( 'content', 'read', $location->getContentInfo(), $location )
                        && !$this->repository->canUser( 'content', 'view_embed', $location->getContentInfo(), $location )
                    )
                    {
                        throw new UnauthorizedException( 'content', 'read', array( 'locationId' => $location->id ) );
                    }

                    $controllerReference = new ControllerReference( 'ez_content:renderLocation', array( 'locationId' => $location->id, 'viewType' => $view, 'params' => $parameters ) );
                    $embedContent = $this->fragmentHandler->render( $controllerReference );
                }
                catch ( APINotFoundException $e )
                {
                    if ( $this->logger )
                    {
                        $this->logger->error(
                            "While generating embed for xmltext, could not locate " .
                            "Location with ID " . $locationId
                        );
                    }
                }
            }

            if ( $embedContent === null )
            {
                // Remove tmp paragraph
                if ( $embed->parentNode->lookupNamespaceUri( 'tmp' ) !== null )
                {
                    $embed->parentNode->parentNode->removeChild( $embed->parentNode );
                }
                // Remove empty link
                else if ( $embed->parentNode->localName === "link" && $embed->parentNode->childNodes->length === 1 )
                {
                    // Remove paragraph with empty link
                    if (
                        $embed->parentNode->parentNode->localName === "paragraph" &&
                        $embed->parentNode->parentNode->childNodes->length === 1
                    )
                    {
                        $embed->parentNode->parentNode->parentNode->removeChild( $embed->parentNode->parentNode );
                    }
                    // Remove empty link
                    else
                    {
                        $embed->parentNode->parentNode->removeChild( $embed->parentNode );
                    }
                }
                // Remove empty embed
                else
                {
                    $embed->parentNode->removeChild( $embed );
                }
            }
            else
            {
                $embed->appendChild( $xmlDoc->createCDATASection( $embedContent ) );
            }
        }
    }

    /**
     * Converts embed elements in $xmlDoc from internal representation to HTML5
     *
     * @param \DOMDocument $xmlDoc
     *
     * @return null
     */
    public function convert( DOMDocument $xmlDoc )
    {
        $this->processTag( $xmlDoc, 'embed' );
        $this->processTag( $xmlDoc, 'embed-inline' );
    }
}
