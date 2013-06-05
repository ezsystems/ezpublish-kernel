<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter\EmbedToHtml5 class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter;
use eZ\Publish\Core\MVC\Symfony\View\Manager;
use eZ\Publish\API\Repository\Repository;
use DOMDocument;

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
     * @var \eZ\Publish\Core\MVC\Symfony\View\Manager
     */
    protected $viewManager;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    public function __construct( Manager $viewManager, Repository $repository, array $excludedAttributes )
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
                $embedContent = $this->viewManager->renderContent(
                    $this->repository->getContentService()->loadContent( $contentId ),
                    $view,
                    $parameters
                );
            }
            else if ( $locationId = $embed->getAttribute( "node_id" ) )
            {
                $embedContent = $this->viewManager->renderLocation(
                    $this->repository->getLocationService()->loadLocation( $locationId ),
                    $view,
                    $parameters
                );
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
