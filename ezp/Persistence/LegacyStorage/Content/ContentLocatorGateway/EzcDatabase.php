<?php
/**
 * File containing the EzcDatabase content locator gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content\ContentLocatorGateway;
use ezp\Persistence\LegacyStorage\Content\ContentLocatorGateway,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Criterion;

/**
 * Content locator gateway implementation using the zeta database component.
 */
class EzcDatabase extends ContentLocatorGateway
{
    /**
     * Database handler
     *
     * @var \ezcDbHandler
     */
    protected $handler;

    /**
     * Construct from database handler
     *
     * @param \ezcDbHandler $handler
     * @return void
     */
    public function __construct( \ezcDbHandler $handler )
    {
        $this->handler = $handler;
    }

    /**
     * Returns a list of object satisfying the $criterion.
     *
     * @param Criterion $criterion
     * @param int $offset
     * @param int $limit
     * @param $sort
     * @return array(ezp\Persistence\Content) Content value object.
     */
    public function find( Criterion $criterion, $offset, $limit, $sort )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( 'id' )
            ->from( 'ezcontentobject' )
            ->where(
                $this->convertCriteria( $query, $criterion )
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

    /**
     * Generic converter of criteria into query fragments
     *
     * @param ezcDatabaseSelectQuery $query
     * @param Criterion $criterion
     * @return ezcDatabaseExpression
     */
    protected function convertCriteria( $query, Criterion $criterion )
    {
        // @TODO: Refactor
        switch ( true )
        {
            case $criterion instanceof Criterion\ContentId:
                return $query->expr->in( 'id', $criterion->value );

            case $criterion instanceof Criterion\LogicalAnd:
                $subexpressions = array();
                foreach ( $criterion->criteria as $subCriterion )
                {
                    $subexpressions[] = $this->convertCriteria( $query, $subCriterion );
                }
                return $query->expr->lAnd( $subexpressions );

            case $criterion instanceof Criterion\LogicalOr:
                $subexpressions = array();
                foreach ( $criterion->criteria as $subCriterion )
                {
                    $subexpressions[] = $this->convertCriteria( $query, $subCriterion );
                }
                return $query->expr->lOr( $subexpressions );

            case $criterion instanceof Criterion\LogicalNot:
                return $query->expr->not(
                    $this->convertCriteria( $query, $criterion->criteria[0] )
                );

            default:
                throw new \RuntimeException( 'No conversion for criterion found.' );
        }
    }
}

