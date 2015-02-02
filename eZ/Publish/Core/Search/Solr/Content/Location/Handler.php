<?php
/**
 * File containing the Location Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Location;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Search\Content\Location\Handler as SearchHandlerInterface;
use eZ\Publish\SPI\Search\FieldType;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\Search\Solr\Content\FieldNameGenerator;

/**
 *
 */
class Handler implements SearchHandlerInterface
{
    /**
     * Content locator gateway.
     *
     * @var \eZ\Publish\Core\Search\Solr\Content\Location\Gateway
     */
    protected $gateway;

    /**
     * Field name generator
     *
     * @var \eZ\Publish\Core\Search\Solr\Content\FieldNameGenerator
     */
    protected $fieldNameGenerator;

    /**
     * Creates a new content handler.
     *
     * @param \eZ\Publish\Core\Search\Solr\Content\Location\Gateway $gateway
     * @param \eZ\Publish\Core\Search\Solr\Content\FieldNameGenerator $fieldNameGenerator
     */
    public function __construct(
        Gateway $gateway,
        FieldNameGenerator $fieldNameGenerator
    )
    {
        $this->gateway = $gateway;
        $this->fieldNameGenerator = $fieldNameGenerator;
    }

    /**
     * Finds Locations for the given $query
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findLocations( LocationQuery $query )
    {
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        return $this->gateway->findLocations( $query );
    }

    /**
     * Indexes a Location in the index storage
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function indexLocation( Location $location )
    {

    }

    /**
     * Deletes a Location from the index storage
     *
     * @param int|string $locationId
     */
    public function deleteLocation( $locationId )
    {

    }

    /**
     * Deletes a Content from the index storage
     *
     * @param $contentId
     */
    public function deleteContent( $contentId )
    {

    }
}
