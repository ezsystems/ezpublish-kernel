<?php
/**
 * File containing the EzcDatabase criterion handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content\ContentLocatorGateway;
use ezp\Persistence\LegacyStorage\Content\ContentLocatorGateway,
    ezp\Persistence\Content\Criterion;

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
        Criterion\Operator::EQ      => "eq",
        Criterion\Operator::GT      => "gt",
        Criterion\Operator::GTE     => "gte",
        Criterion\Operator::LT      => "lt",
        Criterion\Operator::LTE     => "lte",
    );

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param Criterion $criterion
     * @return bool
     */
    abstract public function accept( Criterion $criterion );

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param CriteriaConverter $converter
     * @param \ezcQuerySelect $query
     * @param Criterion $criterion
     * @return \ezcQueryExpression
     */
    abstract public function handle( CriteriaConverter $converter, \ezcQuerySelect $query, Criterion $criterion );
}

