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
     * @var \eZ\Publish\Core\MVC\Symfony\View\Manager
     */
    protected $viewManager;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    public function __construct( Manager $viewManager, Repository $repository )
    {
        $this->viewManager = $viewManager;
        $this->repository = $repository;
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
        foreach ( $xmlDoc->getElementsByTagName( "embed" ) as $embed )
        {
            if ( !$view = $embed->getAttribute( "view" ) )
            {
                $view = "embed";
            }

            $embedContent = null;
            $parameters = array(
                "noLayout" => true,
            );

            if ( $attribute = $embed->getAttribute( "size" ) )
            {
                $parameters["size"] = $attribute;
            }

            $customNS = "http://ez.no/namespaces/ezpublish3/custom/";
            if ( $attribute = $embed->getAttributeNS( $customNS, "offset" ) )
            {
                $parameters["offset"] = $attribute;
            }
            if ( $attribute = $embed->getAttributeNS( $customNS, "limit" ) )
            {
                $parameters["limit"] = $attribute;
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
}
