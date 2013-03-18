<?php
/**
 * File containing the Content Search Gateway class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * The Content Search Gateway provides the implementation for one database to
 * retrieve the desired content objects.
 */
abstract class Gateway
{
    /**
     * Finds content objects for the given query.
     *
     * @todo define structs for the field filters
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    abstract public function findContent( Query $query, array $fieldFilters = array() );

    /**
     * Indexes a content object
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Field[] $document
     *
     * @return void
     */
    abstract public function indexContent( array $document );

    /**
     * Deletes a content object from the index
     *
     * @param int content id
     * @param int|null version id
     *
     * @return void
     */
    abstract public function deleteContent( $contentId, $versionId = null );

    /**
     * Purges all contents from the index
     *
     * @return void
     */
    abstract public function purgeIndex();
}

