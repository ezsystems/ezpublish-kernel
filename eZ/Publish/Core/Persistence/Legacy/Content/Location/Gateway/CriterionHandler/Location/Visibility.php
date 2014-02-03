<?php
/**
 * File containing the DoctrineDatabase location id criterion handler class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\CriterionHandler\Location;

use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use RuntimeException;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use PDO;

/**
 * Location visibility criterion handler
 */
class Visibility extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion$criterion
     *
     * @return boolean
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\Location\Visibility;
    }

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion$criterion
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle( CriteriaConverter $converter, SelectQuery $query, Criterion $criterion )
    {
        $column = $this->dbHandler->quoteColumn( 'is_invisible', 'ezcontentobject_tree' );

        switch ( $criterion->value[0] )
        {
            case Criterion\Location\Visibility::VISIBLE:
                return $query->expr->eq(
                    $column,
                    $query->bindValue( 0, null, PDO::PARAM_INT )
                );

            case Criterion\Location\Visibility::HIDDEN:
                return $query->expr->eq(
                    $column,
                    $query->bindValue( 1, null, PDO::PARAM_INT )
                );

            default:
                throw new RuntimeException(
                    "Unknown value '{$criterion->value[0]}' for Visibility criterion handler."
                );
        }
    }
}

