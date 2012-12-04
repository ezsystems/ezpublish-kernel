<?php
/**
 * File containing the EzcDatabase criterion handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator as CriterionOperator,
    ezcQuerySelect;

/**
 * Content locator gateway implementation using the zeta database component.
 */
abstract class CriterionHandler
{
    /**
     * Map of criterion operators to the respective function names in the zeta
     * Database abstraction layer.
     *
     * @var array
     */
    protected $comparatorMap = array(
        CriterionOperator::EQ => "eq",
        CriterionOperator::GT => "gt",
        CriterionOperator::GTE => "gte",
        CriterionOperator::LT => "lt",
        CriterionOperator::LTE => "lte",
    );

    /**
     * Database handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Creates a new criterion handler
     *
     * @param \EzcDbHandler $dbHandler
     */
    public function __construct( EzcDbHandler $dbHandler )
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param Criterion $criterion
     *
     * @return boolean
     */
    abstract public function accept( Criterion $criterion );

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param CriteriaConverter $converter
     * @param \ezcQuerySelect $query
     * @param Criterion $criterion
     *
     * @return \ezcQueryExpression
     */
    abstract public function handle( CriteriaConverter $converter, ezcQuerySelect $query, Criterion $criterion );
}

