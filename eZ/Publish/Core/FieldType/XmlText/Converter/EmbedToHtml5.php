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
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use Psr\Log\LoggerInterface;
use DOMElement;

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

    /**
     * Prefix of the link attribute names potentially copied to the embed element.
     *
     * @var string
     */
    protected $tmpPrefix = "ezlegacytmp-embed-link-";

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
            $parameters = $this->getParameters( $embed );

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

                    $embedContent = $this->fragmentHandler->render(
                        new ControllerReference(
                            'ez_content:embedContent',
                            array(
                                'contentId' => $contentId,
                                'viewType' => $view,
                                'layout' => false,
                                'params' => $parameters
                            )
                        )
                    );
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

                    $embedContent = $this->fragmentHandler->render(
                        new ControllerReference(
                            'ez_content:embedLocation',
                            array(
                                'locationId' => $locationId,
                                'viewType' => $view,
                                'layout' => false,
                                'params' => $parameters
                            )
                        )
                    );
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
                // Remove empty embed
                $embed->parentNode->removeChild( $embed );
            }
            else
            {
                $embed->appendChild( $xmlDoc->createCDATASection( $embedContent ) );
            }
        }
    }

    /**
     * Returns embed's parameters.
     *
     * @param \DOMElement $embed
     *
     * @return array
     */
    protected function getParameters( DOMElement $embed )
    {
        $parameters = array(
            "noLayout" => true,
            "objectParameters" => array(),
        );

        $linkParameters = $this->getLinkParameters( $embed );

        if ( $linkParameters !== null )
        {
            $parameters["linkParameters"] = $linkParameters;
        }

        foreach ( $embed->attributes as $attribute )
        {
            // We only consider tags in the custom namespace, and skip disallowed names
            if (
                !isset( $this->excludedAttributes[$attribute->localName] )
                && $attribute->localName !== 'url'
                && strpos( $attribute->localName, 'ezlegacytmp-embed-link-' ) !== 0
            )
            {
                $parameters["objectParameters"][$attribute->localName] = $attribute->nodeValue;
            }
        }

        return $parameters;
    }

    /**
     * Returns embed's link parameters, or null if embed is not linked.
     *
     * @param \DOMElement $embed
     *
     * @return array|null
     */
    protected function getLinkParameters( DOMElement $embed )
    {
        if ( !$embed->hasAttribute( "url" ) )
        {
            return null;
        }

        $tmpPrefix = $this->tmpPrefix;

        $target = $embed->getAttribute( "{$tmpPrefix}target" );
        $title = $embed->getAttribute( "{$tmpPrefix}title" );
        $id = $embed->getAttribute( "{$tmpPrefix}id" );
        $class = $embed->getAttribute( "{$tmpPrefix}class" );
        $resourceFragmentIdentifier = $embed->getAttribute( "{$tmpPrefix}anchor_name" );
        $resourceType = null;
        $resourceId = null;

        if ( $embed->hasAttribute( "{$tmpPrefix}object_id" ) )
        {
            $resourceType = "CONTENT";
            $resourceId = $embed->getAttribute( "{$tmpPrefix}object_id" );
        }
        else if ( $embed->hasAttribute( "{$tmpPrefix}node_id" ) )
        {
            $resourceType = "LOCATION";
            $resourceId = $embed->getAttribute( "{$tmpPrefix}node_id" );
        }
        else if ( $embed->hasAttribute( "{$tmpPrefix}url_id" ) )
        {
            $resourceType = "URL";
            $resourceId = $embed->getAttribute( "{$tmpPrefix}url_id" );
        }
        else
        {
            $this->logger->error(
                "Could not resolve XmlText embed link resource type and ID"
            );
        }

        $parameters = array(
            "href" => $embed->getAttribute( "url" ),
            "resourceType" => $resourceType,
            "resourceId" => $resourceId,
            "wrapped" => $this->isLinkWrapped( $embed ),
        );

        if ( !empty( $resourceFragmentIdentifier ) )
        {
            $parameters["resourceFragmentIdentifier"] = $resourceFragmentIdentifier;
        }

        if ( !empty( $target ) )
        {
            $parameters["target"] = $target;
        }

        if ( !empty( $title ) )
        {
            $parameters["title"] = $title;
        }

        if ( !empty( $id ) )
        {
            $parameters["id"] = $id;
        }

        if ( !empty( $class ) )
        {
            $parameters["class"] = $class;
        }

        return $parameters;
    }

    /**
     * Returns boolean signifying if the embed is contained in a link element of not.
     *
     * After EmbedLinking converter pass this should be possible only for inline level embeds.
     *
     * @param \DOMElement $element
     *
     * @return boolean
     */
    protected function isLinkWrapped( DOMElement $element )
    {
        $parentNode = $element->parentNode;

        if ( $parentNode instanceof DOMDocument )
        {
            return false;
        }
        else if ( $parentNode->localName === "link" )
        {
            $childCount = 0;

            /** @var \DOMText|\DOMElement $node */
            foreach ( $parentNode->childNodes as $node )
            {
                if ( !( $node->nodeType === XML_TEXT_NODE && $node->isWhitespaceInElementContent() ) )
                {
                    $childCount += 1;
                }
            }

            if ( $childCount === 1 )
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        return $this->isLinkWrapped( $parentNode );
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
