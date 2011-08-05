<?php
/**
 * File containing the EzcDatabase content locator gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\SearchHandler\Gateway;
use ezp\Persistence\Storage\Legacy\Content\SearchHandler\Gateway,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Criterion;

/**
 * Content locator gateway implementation using the zeta handler component.
 */
class EzcDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @var \ezcDbHandler
     */
    protected $handler;

    /**
     * Criteria converter
     *
     * @var CriteriaConverter
     */
    protected $converter;

    /**
     * Construct from handler handler
     *
     * @param \ezcDbHandler $handler
     * @return void
     */
    public function __construct( \ezcDbHandler $handler, CriteriaConverter $converter )
    {
        $this->handler   = $handler;
        $this->converter = $converter;
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
        $query = $this->handler->createSelectQuery();
        $query
            ->select( 'id' )
            ->from( 'ezcontentobject' )
            ->where(
                $this->converter->convertCriteria( $query, $criterion )
            )
            ->limit( $limit, $offset );

        $statement = $query->prepare();
        $statement->execute();
        $objects = array();
        while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            $content = new \ezp\Persistence\Content();
            $content->id = $row['id'];
            $objects[] = $content;
        }

        return $objects;
    }
}

