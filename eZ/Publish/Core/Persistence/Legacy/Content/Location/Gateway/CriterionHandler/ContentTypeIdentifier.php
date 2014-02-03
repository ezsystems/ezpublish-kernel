<?php
/**
 * File containing the DoctrineDatabase content type criterion handler class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\CriterionHandler;

use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Content type criterion handler
 */
class ContentTypeIdentifier extends CriterionHandler
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
        return $criterion instanceof Criterion\ContentTypeIdentifier;
    }

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle( CriteriaConverter $converter, SelectQuery $query, Criterion $criterion )
    {
        $subSelect = $query->subSelect();
        $subSelect
            ->select( $this->dbHandler->quoteColumn( 'id' ) )
            ->from( $this->dbHandler->quoteTable( 'ezcontentclass' ) )
            ->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn( 'identifier' ),
                    $criterion->value
                )
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn( 'contentclass_id', 'ezcontentobject' ),
            $subSelect
        );
    }
}

