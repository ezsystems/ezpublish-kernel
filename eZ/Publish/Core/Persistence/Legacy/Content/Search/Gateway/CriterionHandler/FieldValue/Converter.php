<?php
/**
 * File containing the ValueConverter class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\Handler;
use ezcQuerySelect;
use RuntimeException;
use OutOfBoundsException;

/**
 * Content locator gateway implementation using the zeta database component.
 */
class Converter
{
    /**
     * Criterion field value handler registry
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\HandlerRegistry
     */
    protected $registry;

    /**
     * Default Criterion field value handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\Handler
     */
    protected $defaultHandler;

    /**
     * Construct from an array of Criterion field value handlers
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\HandlerRegistry $registry
     * @param null|\eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\Handler $defaultHandler
     */
    public function __construct( HandlerRegistry $registry, Handler $defaultHandler = null )
    {
        $this->registry = $registry;
        $this->defaultHandler = $defaultHandler;
    }

    /**
     * Converts the criteria into query fragments
     *
     * @throws \RuntimeException if Criterion is not applicable to its target
     *
     * @param string $fieldTypeIdentifier
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $column
     *
     * @return \ezcQueryExpression
     */
    public function convertCriteria( $fieldTypeIdentifier, ezcQuerySelect $query, Criterion $criterion, $column )
    {
        if ( $this->registry->has( $fieldTypeIdentifier ) )
        {
            return $this->registry->get( $fieldTypeIdentifier )->handle( $query, $criterion, $column );
        }

        if ( $this->defaultHandler === null )
        {
            throw new RuntimeException( "No conversion for a field type '$fieldTypeIdentifier' found." );
        }

        return $this->defaultHandler->handle( $query, $criterion, $column );
    }
}
