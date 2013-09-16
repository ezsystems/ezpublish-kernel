<?php
/**
 * File containing the Criterion ValueHandlerRegistry class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue;

use OutOfBoundsException;

/**
 * Registry for Criterion field value handlers.
 */
class HandlerRegistry
{
    /**
     * Map of Criterion field value handlers where key is field type identifier
     * and value is field value handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\Handler[]
     */
    protected $map = array();

    /**
     * Create field value handler registry with handler map.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\Handler[] $map
     *        Map of Criterion field value handlers where key is field type identifier and value field value handler
     */
    public function __construct( array $map )
    {
        foreach ( $map as $fieldTypeIdentifier => $handler )
        {
            $this->register( $fieldTypeIdentifier, $handler );
        }
    }

    /**
     * Register $handler for $fieldTypeIdentifier.
     *
     * @param string $fieldTypeIdentifier
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\Handler $handler
     *
     * @return void
     */
    public function register( $fieldTypeIdentifier, $handler )
    {
        $this->map[$fieldTypeIdentifier] = $handler;
    }

    /**
     * Returns handler for given $fieldTypeIdentifier.
     *
     * @throws \OutOfBoundsException If handler is not registered for a given $fieldTypeIdentifier
     *
     * @param string $fieldTypeIdentifier
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\Handler
     */
    public function get( $fieldTypeIdentifier )
    {
        if ( !isset( $this->map[$fieldTypeIdentifier] ) )
        {
            throw new OutOfBoundsException( "No handler registered for field type '{$fieldTypeIdentifier}'." );
        }

        return $this->map[$fieldTypeIdentifier];
    }
}
