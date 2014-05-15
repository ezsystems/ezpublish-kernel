<?php
/**
 * File containing the DoctrineDatabase FieldRelation criterion handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use RuntimeException;

/**
 * FieldRelation criterion handler
 */
class FieldRelation extends CriterionHandler
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
        return $criterion instanceof Criterion\FieldRelation;
    }

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     * @throws RuntimeException
     */
    public function handle( CriteriaConverter $converter, SelectQuery $query, Criterion $criterion )
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

