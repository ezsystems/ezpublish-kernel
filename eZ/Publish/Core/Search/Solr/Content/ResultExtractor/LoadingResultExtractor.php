<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\ResultExtractor;

use eZ\Publish\Core\Search\Solr\Content\ResultExtractor;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\Core\Search\Solr\Content\FacetBuilderVisitor;
use RuntimeException;

/**
 * The Loading Extractor extracts the value object from the Elasticsearch search hit data
 * by loading it from the database.
 */
class LoadingResultExtractor extends ResultExtractor
{
    /**
     * Content handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * Location handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    public function __construct(
        ContentHandler $contentHandler,
        LocationHandler $locationHandler,
        FacetBuilderVisitor $facetBuilderVisitor
    )
    {
        $this->contentHandler = $contentHandler;
        $this->locationHandler = $locationHandler;

        parent::__construct( $facetBuilderVisitor );
    }

    /**
     * Extracts value object from $hit returned by Solr backend.
     *
     * @throws \RuntimeException If search $hit could not be handled
     *
     * @param mixed $hit
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    public function extractHit( $hit )
    {
        if ( $hit->document_type_id === "content" )
        {
            return $this->contentHandler->loadContentInfo( $hit->content_id );
        }

        if ( $hit->document_type_id === "location" )
        {
            return $this->locationHandler->load( $hit->location_id );
        }

        throw new RuntimeException(
            "Could not extract: document of type '{$hit->document_type_id}' is not handled."
        );
    }
}
