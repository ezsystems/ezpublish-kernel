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
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;

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
     * @var \eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface
     */
    protected $viewManager;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    public function __construct( ViewManagerInterface $viewManager, Repository $repository, array $excludedAttributes )
    {
        $this->viewManager = $viewManager;
        $this->repository = $repository;
        $this->excludedAttributes = array_fill_keys( $excludedAttributes, true );
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

                $embedContent = $this->viewManager->renderContent( $content, $view, $parameters );
            }
            else if ( $locationId = $embed->getAttribute( "node_id" ) )
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

                $embedContent = $this->viewManager->renderLocation( $location, $view, $parameters );
            }

            if ( $embedContent !== null )
                $embed->appendChild( $xmlDoc->createCDATASection( $embedContent ) );
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
