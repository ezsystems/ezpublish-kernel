<?php
/**
 * File containing the EzcDatabase RelationList criterion handler class
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
 * RelationList criterion handler
 */
class RelationList extends CriterionHandler
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
        return $criterion instanceof Criterion\RelationList;
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
     * @throws RuntimeException
     */
    public function handle( CriteriaConverter $converter, ezcQuerySelect $query, Criterion $criterion )
    {
        $column = $this->dbHandler->quoteColumn( 'to_contentobject_id', 'ezcontentobject_link' );

        switch ( $criterion->operator )
        {
            case Criterion\Operator::CONTAINS:
                if ( count( $criterion->value ) > 1 )
                {
                    $subRequest = array();

                    foreach ( $criterion->value as $value )
                    {
                        $subSelect = $query->subSelect();

                        $subSelect->select(
                            $this->dbHandler->quoteColumn( 'from_contentobject_id' )
                        )->from(
                            $this->dbHandler->quoteTable( 'ezcontentobject_link' )
                        );

                        $subSelect->innerJoin(
                            'ezcontentclass_attribute',
                            $subSelect->expr->eq( 'ezcontentclass_attribute.id', 'ezcontentobject_link.contentclassattribute_id' )
                        );

                        $subSelect->where(
                            $subSelect->expr->lAnd(
                                $subSelect->expr->eq(
                                    $this->dbHandler->quoteColumn( 'from_contentobject_version', 'ezcontentobject_link' ),
                                    $this->dbHandler->quoteColumn( 'current_version', 'ezcontentobject' )
                                ),
                                $subSelect->expr->eq(
                                    $this->dbHandler->quoteColumn( 'contentclass_id', 'ezcontentclass_attribute' ),
                                    $this->dbHandler->quoteColumn( 'contentclass_id', 'ezcontentobject' )
                                ),
                                $subSelect->expr->eq(
                                    $this->dbHandler->quoteColumn( 'identifier', 'ezcontentclass_attribute' ),
                                    $subSelect->bindValue( $criterion->target )
                                ),
                                $subSelect->expr->eq(
                                    $column,
                                    $value
                                )
                            )
                        );

                        $subRequest[] = $subSelect->expr->in(
                            $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
                            $subSelect
                        );
                    }

                    return $query->expr->lAnd(
                        $subRequest
                    );
                }

            case Criterion\Operator::IN:
                $subSelect = $query->subSelect();

                $subSelect->select(
                    $this->dbHandler->quoteColumn( 'from_contentobject_id' )
                )->from(
                    $this->dbHandler->quoteTable( 'ezcontentobject_link' )
                );

                $subSelect->innerJoin(
                    'ezcontentclass_attribute',
                    $subSelect->expr->eq( 'ezcontentclass_attribute.id', 'ezcontentobject_link.contentclassattribute_id' )
                );

                return $query->expr->in(
                    $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
                    $subSelect->where(
                        $subSelect->expr->lAnd(
                            $subSelect->expr->eq(
                                $this->dbHandler->quoteColumn( 'from_contentobject_version', 'ezcontentobject_link' ),
                                $this->dbHandler->quoteColumn( 'current_version', 'ezcontentobject' )
                            ),
                            $subSelect->expr->eq(
                                $this->dbHandler->quoteColumn( 'contentclass_id', 'ezcontentclass_attribute' ),
                                $this->dbHandler->quoteColumn( 'contentclass_id', 'ezcontentobject' )
                            ),
                            $subSelect->expr->eq(
                                $this->dbHandler->quoteColumn( 'identifier', 'ezcontentclass_attribute' ),
                                $subSelect->bindValue( $criterion->target )
                            ),
                            $subSelect->expr->in(
                                $column,
                                $criterion->value
                            )
                        )
                    )
                );

            default:
                throw new RuntimeException( "Unknown operator '{$criterion->operator}' for RelationList criterion handler." );
        }
    }
}

