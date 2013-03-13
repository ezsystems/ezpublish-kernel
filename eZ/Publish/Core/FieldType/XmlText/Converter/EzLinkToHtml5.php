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
use eZ\Publish\Core\MVC\Symfony\View\Manager;
use eZ\Publish\API\Repository\Repository;
use DOMDocument;

class EzLinkToHtml5 implements Converter
{

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    public function __construct( Repository $repository )
    {
        $this->repository = $repository;
    }

    /**
     * Converts internal links (eznode:// and ezobject://) to URLs.
     *
     * @param \DOMDocument $xmlDoc
     *
     * @return string|null
     */
    public function convert(DOMDocument $xmlDoc)
    {
        $locationService = $this->repository->getLocationService();
        $contentService = $this->repository->getContentService();
        $urlAliasService = $this->repository->getURLAliasService();

        foreach ( $xmlDoc->getElementsByTagName( "link" ) as $link )
        {
            $location = false;
            
            if ($link->hasAttribute('object_id'))
            {
                $content = $contentService->loadContent($link->getAttribute('object_id'));
                if ($content)
                {
                    $location = $locationService->loadLocation($content->contentInfo->mainLocationId);

                }
            }

            if ($link->hasAttribute('node_id'))
            {
                $location = $locationService->loadLocation($link->getAttribute('node_id'));
            }

            if ($location)
            {
                $urlAlias = $urlAliasService->reverseLookup($location);
                $link->setAttribute('url', $urlAlias->path);
            }

        }

        return null;
    }

}