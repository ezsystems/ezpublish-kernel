<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Search;

use eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * The Content Search handler retrieves sets of of Content objects, based on a
 * set of criteria.
 */
abstract class Handler
{
    /**
     * Returns a list of object satisfying the $criterion.
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param int $offset
     * @param int|null $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sort
     * @param string[] $translations
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Result
     */
    abstract public function find( Criterion $criterion, $offset = 0, $limit = null, array $sort = null, $translations = null );

    /**
     * Returns a single Content object found.
     *
     * Performs a {@link find()} query to find a single object. You need to
     * ensure, that your $criterion ensure that only a single object can be
     * retrieved.
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string[] $translations
     * @return \eZ\Publish\SPI\Persistence\Content
     * @todo Define exceptions (InMemory uses NotFound while Legazy uses InvalidObjectCount)
     */
    abstract public function findSingle( Criterion $criterion, $translations = null );

    /**
     * Indexes a content object
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @return void
     */
    abstract public function indexContent( Content $content );
}
