<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter\Output\EmbedToHtml5 class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter\Output;

use eZ\Publish\Core\FieldType\XmlText\Converter,
    eZ\Publish\Core\MVC\Symfony\View\Manager,
    eZ\Publish\Core\Repository\Repository,
    DOMDocument;

/**
 * Converts internal
 */
class EmbedToHtml5 implements Converter
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\Manager
     */
    protected $viewManager;

    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    protected $repository;

    public function __construct( Manager $viewManager, Repository $repository )
    {
        $this->viewManager = $viewManager;
        $this->repository = $repository;
    }

    /**
     * Convert embed elements in $xmlString from internal representation to HTML5
     *
     * @param string $xmlString
     * @return string
     */
    public function convert( $xmlString )
    {
        $doc = new DOMDocument;
        $doc->loadXML( $xmlString );

        foreach ( $doc->getElementsByTagName( "embed" ) as $embed )
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
                $embed->appendChild( $doc->createCDATASection( $embedContent ) );
        }

        return $doc->saveXML();
    }
}
