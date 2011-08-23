<?php
/**
 * File containing the EzcDatabase content locator gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Search\Gateway;
use ezp\Persistence\Storage\Legacy\Content\Search\Gateway,
    ezp\Persistence\Storage\Legacy\EzcDbHandler,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Search,
    ezp\Persistence\Content\Criterion;

/**
 * Content locator gateway implementation using the zeta handler component.
 */
class EzcDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @var EzcDbHandler
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
     * @param EzcDbHandler $handler
     * @return void
     */
    public function __construct( EzcDbHandler $handler, CriteriaConverter $converter )
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
        $result    = new Search\Result();

        // Get full object count
        $query = $this->handler->createSelectQuery();
        $condition = $this->converter->convertCriteria( $query, $criterion );

        $query
            ->select( 'COUNT( * )' )
            ->from( $this->handler->quoteTable( 'ezcontentobject' ) )
            ->where( $condition );
        $statement = $query->prepare();
        $statement->execute();

        $count         = $statement->fetchAll( \PDO::FETCH_COLUMN, 0 );
        $result->count = (int) reset( $count );

        // Fetch actual content objects
        $query->reset();
        $query
            ->select( $this->handler->quoteColumn( 'id' ) )
            ->from( $this->handler->quoteTable( 'ezcontentobject' ) )
            ->where( $condition )
            ->limit( $limit, $offset );

        $statement = $query->prepare();
        $statement->execute();
        while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            $content = new \ezp\Persistence\Content();
            $content->id = $row['id'];
            $result->content[] = $content;
        }

        return $result;
    }
}

