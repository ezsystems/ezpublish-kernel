<?php
/**
 * File containing the ContentLocator class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content;

use ezp\Persistence\Content,
    ezp\Persistence\Content\Criterion;

/**
 * The ContentLocator retrieves sets of of Content objects, based on a set of
 * criteria.
 *
 * The basic idea of this class is to do the following:
 *
 * 1) The find methods retrieve a recursive set of filters, which define which
 * content objects to retrieve from the database. Those may be combined using
 * boolean opeartors.
 *
 * 2) This recursive criterion definition is visited into a query, which limits
 * the content retrieved from the database. We might not be able to create
 * sensible queries from all criterion definitions.
 *
 * 3) The query might be possible to optimize (remove empty statements),
 * reduce singular and and or constructs…
 *
 * 4) Additionally we might need a post-query filtering step, which filters
 * content objects based on criteria, which could not be convertedd in to
 * database statements.
 *
 * @version //autogentag//
 */
class ContentLocator
{
    /**
     * Content locator gateway.
     *
     * @var ContentLocatorGateway
     */
    protected $gateway;

    /**
     * Creates a new content handler.
     *
     * @param \ezp\Persistence\LegacyStorage\Content\ContentLocatorGateway $gateway
     */
    public function __construct( ContentLocatorGateway $gateway )
    {
        $this->gateway = $gateway;
    }

    /**
     * Returns a list of object satisfying the $criterion.
     *
     * @param Criterion $criterion
     * @param int $offset
     * @param int|null $limit
     * @param $sort
     * @return array(ezp\Persistence\Content) Content value object.
     */
    public function find( Criterion $criterion, $offset = 0, $limit = null, $sort = null )
    {
        return $this->gateway->find( $criterion, $offset, $limit, $sort );
    }

    /**
     * Returns a single Content object found.
     *
     * Performs a {@link find()} query to find a single object. You need to
     * ensure, that your $criterion ensure that only a single object can be
     * retrieved.
     *
     * @param Criterion $criterion
     * @return \ezp\Persistence\Content
     */
    public function findSingle( Criterion $criterion )
    {
        throw new \Exception( "Not implemented yet." );
    }
}

