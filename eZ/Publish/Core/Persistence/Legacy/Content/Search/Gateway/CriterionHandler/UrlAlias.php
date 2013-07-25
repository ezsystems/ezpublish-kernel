<?php
/**
 * File containing the EzcDatabase UrlAlias criterion handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use ezcQuerySelect;
use RuntimeException;

/**
 * UrlAlias criterion handler
 */
class UrlAlias extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\UrlAlias;
    }

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter $converter
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \ezcQueryExpression
     */
    public function handle( CriteriaConverter $converter, ezcQuerySelect $query, Criterion $criterion )
    {
        $subSelect = $query->subSelect();
        $subSelect->select(
            $this->dbHandler->quoteColumn( "contentobject_id", "ezcontentobject_tree" )
        )->from(
            $this->dbHandler->quoteTable( 'ezcontentobject_tree' )
        );

        switch ( $criterion->operator )
        {
            case Criterion\Operator::IN:
                $this->applyMultipleUrlAliasConditions( $subSelect, $criterion->value );
                break;

            case Criterion\Operator::EQ:
                $this->applySingleUrlAliasConditions( $subSelect, $criterion->value );
                break;

            case Criterion\Operator::LIKE:
                $this->applySingleUrlAliasConditions( $subSelect, $criterion->value, false );
                break;

            default:
                throw new RuntimeException( 'Unknown operator.' );
        }

        return $query->expr->in(
            $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
            $subSelect
        );
    }

    /**
     * Applies conditions for a single URL on a given $query.
     *
     * @param ezcQuerySelect $query
     * @param string $url
     * @param boolean $strictMatch
     */
    protected function applySingleUrlAliasConditions( ezcQuerySelect $query, $url, $strictMatch = true )
    {
        $query->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( "node_id", "ezcontentobject_tree" ),
                $query->expr->subString( $this->dbHandler->quoteColumn( "action", "ezurlalias_ml" ), 8 )
            )
        );
        $this->applyUrlAliasConditions( $query, reset( $url ), $strictMatch );
    }

    /**
     * Applies conditions for array of URLs on a given $query.
     *
     * @param ezcQuerySelect $query
     * @param array $urls
     *
     * @return void
     */
    protected function applyMultipleUrlAliasConditions( ezcQuerySelect $query, array $urls )
    {
        // Fall back to single match if array contains only one URL
        if ( count( $urls ) === 1 )
        {
            $this->applySingleUrlAliasConditions( $query, $urls, false );
            return;
        }

        $conditions = array();

        foreach ( $urls as $url )
        {
            $subSelect = $query->subSelect();
            $subSelect->select(
                $subSelect->expr->subString( $this->dbHandler->quoteColumn( "action", "ezurlalias_ml" ), 8 )
            );

            $this->applyUrlAliasConditions( $subSelect, $url, false );

            $conditions[] = $query->expr->in(
                $this->dbHandler->quoteColumn( "node_id", "ezcontentobject_tree" ),
                $subSelect
            );
        }

        $query->where( $query->expr->lOr( $conditions ) );
    }

    /**
     *
     *
     * @param \ezcQuerySelect $query
     * @param string $url
     * @param boolean $strictMatch
     *
     * @return void
     */
    protected function applyUrlAliasConditions( ezcQuerySelect $query, $url, $strictMatch = true )
    {
        $urlParts = explode( "/", trim( $url, "/ " ) );
        $count = count( $urlParts );

        foreach ( $urlParts as $level => $urlPart )
        {
            $urlPart = strtolower( $urlPart );
            $tableName = "ezurlalias_ml" . ( $level === $count - 1 ? "" : $level );
            $query->from( $query->alias( "ezurlalias_ml", $tableName ) );

            // Don't fuzzy match if not necessary
            if ( $strictMatch || strpos( $urlPart, "*" ) === false )
            {
                $comparison = $query->expr->eq(
                    $query->expr->lower( $this->dbHandler->quoteColumn( "text", $tableName ) ),
                    $query->bindValue( $urlPart, null, \PDO::PARAM_STR )
                );
            }
            else
            {
                $comparison = $query->expr->like(
                    $query->expr->lower( $this->dbHandler->quoteColumn( "text", $tableName ) ),
                    $query->bindValue( str_replace( "*", "%", $urlPart ), null, \PDO::PARAM_STR )
                );
            }

            $query->where(
                $query->expr->lAnd(
                    // Hierarchy link
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "parent", $tableName ),
                        // root entry has parent column set to 0
                        isset( $previousTableName ) ?
                            $this->dbHandler->quoteColumn( "link", $previousTableName ) :
                            $query->bindValue( 0, null, \PDO::PARAM_INT )
                    ),
                    // Autogenerated
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "is_alias", $tableName ),
                        $query->bindValue( 0, null, \PDO::PARAM_INT )
                    ),
                    // Not history
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "is_original", $tableName ),
                        $query->bindValue( 1, null, \PDO::PARAM_INT )
                    ),
                    // Match url part
                    $comparison
                )
            );

            $previousTableName = $tableName;
        }
    }
}

